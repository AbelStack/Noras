<?php

namespace App\Services;

use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\User;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\GeminiProvider;
use App\Services\AI\GroqProvider;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NoteGenerationService
{
    private PdfTextExtractor $pdfExtractor;
    private AIProviderInterface $aiProvider;

    public function __construct(PdfTextExtractor $pdfExtractor)
    {
        $this->pdfExtractor = $pdfExtractor;
        $this->aiProvider = $this->resolveAIProvider();
    }

    /**
     * Resolve the AI provider based on configuration.
     * 
     * @return AIProviderInterface
     * @throws Exception
     */
    private function resolveAIProvider(): AIProviderInterface
    {
        $providerName = config('services.ai.provider', 'gemini');

        $provider = match ($providerName) {
            'gemini' => new GeminiProvider(),
            'groq' => new GroqProvider(),
            default => throw new Exception("Unsupported AI provider: {$providerName}"),
        };

        if (!$provider->isConfigured()) {
            $alternate = $providerName === 'gemini' ? new GroqProvider() : new GeminiProvider();
            if ($alternate->isConfigured()) {
                Log::info("Primary AI provider '{$providerName}' not configured. Falling back to '{$alternate->getProviderName()}'.");
                return $alternate;
            }

            throw new Exception("AI provider '{$providerName}' is not properly configured. Please check your environment variables.");
        }

        return $provider;
    }

    private function resolveFallbackProvider(string $currentProvider): ?AIProviderInterface
    {
        $fallback = $currentProvider === 'groq' ? new GeminiProvider() : new GroqProvider();
        return $fallback->isConfigured() ? $fallback : null;
    }

    /**
     * Generate notes for a course material for a specific user.
     * 
     * @param CourseMaterial $material
     * @param User $user
     * @param bool $regenerate Whether to regenerate if notes already exist
     * @return GeneratedNote
     * @throws Exception
     */
    public function generateNotes(CourseMaterial $material, User $user, bool $regenerate = false): GeneratedNote
    {
        @set_time_limit(300);
        $startTime = microtime(true);

        try {
            // Check if notes already exist for this material as canonical master note
            $existingNote = GeneratedNote::where('course_material_id', $material->id)->first();

            if ($existingNote && !$regenerate && $existingNote->material_version === $material->version) {
                return $existingNote;
            }

            // Extract text from PDF
            Log::info("Extracting text from PDF for material {$material->id}");
            $pdfText = $this->pdfExtractor->extractText($material);
            $sourceCharCount = $this->pdfExtractor->getCharacterCount($pdfText);

            if ($sourceCharCount < 100) {
                throw new Exception('Insufficient text content for note generation (minimum 100 characters required)');
            }

            Log::info("Extracted {$sourceCharCount} characters from PDF");

            // Generate notes using AI with automatic fallback on rate/token limits
            $activeProvider = $this->resolveAIProvider();
            Log::info("Generating notes using {$activeProvider->getProviderName()} ({$activeProvider->getModelName()})");

            try {
                $notesContent = $activeProvider->generateNotes($pdfText, $material->title);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $isRateOrTokenLimit = stripos($msg, 'rate limit') !== false ||
                    stripos($msg, 'tokens per minute') !== false ||
                    stripos($msg, 'tpm') !== false ||
                    stripos($msg, 'request too large') !== false ||
                    stripos($msg, '429') !== false ||
                    stripos($msg, 'quota') !== false;

                $fallback = $isRateOrTokenLimit ? $this->resolveFallbackProvider($activeProvider->getProviderName()) : null;

                if ($fallback) {
                    Log::warning("Primary AI provider [{$activeProvider->getProviderName()}] hit rate/token limit: {$msg}. Automatically falling back to [{$fallback->getProviderName()}].");
                    $activeProvider = $fallback;
                    $notesContent = $activeProvider->generateNotes($pdfText, $material->title);
                } else {
                    throw $e;
                }
            }

            $notesCharCount = mb_strlen($notesContent, 'UTF-8');

            // Calculate generation time
            $generationTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::info("Generated {$notesCharCount} characters in {$generationTimeMs}ms");

            // Save to database (update if exists, create if not)
            return DB::transaction(function () use (
                $material,
                $user,
                $notesContent,
                $sourceCharCount,
                $notesCharCount,
                $generationTimeMs,
                $existingNote,
                $activeProvider
            ) {
                $data = [
                    'course_material_id' => $material->id,
                    'material_version' => $material->version,
                    'generated_by' => $user->id,
                    'content' => $notesContent,
                    'ai_provider' => $activeProvider->getProviderName(),
                    'ai_model' => $activeProvider->getModelName(),
                    'source_char_count' => $sourceCharCount,
                    'notes_char_count' => $notesCharCount,
                    'generation_time_ms' => $generationTimeMs,
                ];

                if ($existingNote) {
                    $existingNote->update($data);
                    return $existingNote->fresh();
                } else {
                    return GeneratedNote::create($data);
                }
            });

        } catch (Exception $e) {
            Log::error("Note generation failed: {$e->getMessage()}", [
                'material_id' => $material->id,
                'user_id' => $user->id,
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    /**
     * Get all notes generated by a user or available to them with optional filtering.
     * 
     * @param User $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserNotes(User $user, int $perPage = 20)
    {
        $query = GeneratedNote::with(['courseMaterial.courseOffering.course', 'courseMaterial.courseOffering.department'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('generated_by', $user->id)
                  ->orWhere(function ($masterQ) {
                      $masterQ->whereHas('courseMaterial', function ($mq) {
                          $mq->where('status', 'published');
                      })->whereHas('generatedBy', function ($uq) {
                          $uq->where('role', 'admin');
                      });
                  });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a specific note for a user (with authorization check).
     * 
     * @param int $noteId
     * @param User $user
     * @return GeneratedNote|null
     */
    public function getUserNote(int $noteId, User $user): ?GeneratedNote
    {
        $query = GeneratedNote::where('id', $noteId)
            ->with(['courseMaterial.courseOffering.course', 'courseMaterial.courseOffering.department']);

        if (!$user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('generated_by', $user->id)
                  ->orWhere(function ($masterQ) {
                      $masterQ->whereHas('courseMaterial', function ($mq) {
                          $mq->where('status', 'published');
                      })->whereHas('generatedBy', function ($uq) {
                          $uq->where('role', 'admin');
                      });
                  });
            });
        }

        return $query->first();
    }

    /**
     * Delete a note (admin or owner can delete).
     * 
     * @param GeneratedNote $note
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function deleteNote(GeneratedNote $note, User $user): bool
    {
        if (!$user->isAdmin() && $note->generated_by !== $user->id) {
            throw new Exception('Unauthorized to delete this note');
        }

        // Clear study task references before deleting
        \App\Models\StudyTask::where('generated_note_id', $note->id)
            ->update(['generated_note_id' => null]);

        // Clean up polymorphic bookmarks
        \App\Models\Bookmark::where('bookmarkable_type', GeneratedNote::class)
            ->where('bookmarkable_id', $note->id)
            ->delete();

        return $note->delete();
    }

    /**
     * Get the current AI provider information.
     * 
     * @return array
     */
    public function getProviderInfo(): array
    {
        return [
            'provider' => $this->aiProvider->getProviderName(),
            'model' => $this->aiProvider->getModelName(),
            'configured' => $this->aiProvider->isConfigured(),
        ];
    }
}

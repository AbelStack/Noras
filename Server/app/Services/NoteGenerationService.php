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
            throw new Exception("AI provider '{$providerName}' is not properly configured. Please check your environment variables.");
        }

        return $provider;
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
        $startTime = microtime(true);

        try {
            // Check if notes already exist for this user, material, and current version
            $existingNote = GeneratedNote::where('course_material_id', $material->id)
                ->where('material_version', $material->version)
                ->where('generated_by', $user->id)
                ->first();

            if ($existingNote && !$regenerate) {
                return $existingNote;
            }
            
            // Check if there's a note from a previous version
            $oldNote = GeneratedNote::where('course_material_id', $material->id)
                ->where('generated_by', $user->id)
                ->where('material_version', '!=', $material->version)
                ->first();
            
            if ($oldNote) {
                Log::info("Found existing note from version {$oldNote->material_version}, current material is version {$material->version}. Will create new note for current version.");
            }

            // Extract text from PDF
            Log::info("Extracting text from PDF for material {$material->id}");
            $pdfText = $this->pdfExtractor->extractText($material);
            $sourceCharCount = $this->pdfExtractor->getCharacterCount($pdfText);

            Log::info("Extracted {$sourceCharCount} characters from PDF");

            // Generate notes using AI
            Log::info("Generating notes using {$this->aiProvider->getProviderName()} ({$this->aiProvider->getModelName()})");
            $notesContent = $this->aiProvider->generateNotes($pdfText, $material->title);
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
                $existingNote
            ) {
                $data = [
                    'course_material_id' => $material->id,
                    'material_version' => $material->version,
                    'generated_by' => $user->id,
                    'content' => $notesContent,
                    'ai_provider' => $this->aiProvider->getProviderName(),
                    'ai_model' => $this->aiProvider->getModelName(),
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
     * Get all notes generated by a user with optional filtering.
     * 
     * @param User $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserNotes(User $user, int $perPage = 20)
    {
        return GeneratedNote::where('generated_by', $user->id)
            ->with(['courseMaterial.courseOffering.course', 'courseMaterial.courseOffering.department'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
        return GeneratedNote::where('id', $noteId)
            ->where('generated_by', $user->id)
            ->with(['courseMaterial.courseOffering.course', 'courseMaterial.courseOffering.department'])
            ->first();
    }

    /**
     * Delete a note (only the owner can delete).
     * 
     * @param GeneratedNote $note
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function deleteNote(GeneratedNote $note, User $user): bool
    {
        if ($note->generated_by !== $user->id) {
            throw new Exception('Unauthorized to delete this note');
        }

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

<?php

namespace App\Services;

use App\Models\CourseMaterial;
use App\Models\GeneratedQuiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\User;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\GeminiProvider;
use App\Services\AI\GroqProvider;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizGenerationService
{
    private PdfTextExtractor $pdfExtractor;
    private AIProviderInterface $aiProvider;

    public function __construct(PdfTextExtractor $pdfExtractor)
    {
        $this->pdfExtractor = $pdfExtractor;
        $this->aiProvider = $this->resolveAIProvider();
    }

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

    public function generateQuiz(CourseMaterial $material, User $user, int $questionCount = 15, int $optionsPerQuestion = 4): GeneratedQuiz
    {
        @set_time_limit(300);
        // Validate inputs: min 1 (for testing flexibility), max 30
        $questionCount = max(1, min($questionCount, 30));
        $optionsPerQuestion = max(2, min($optionsPerQuestion, 6));

        $startTime = microtime(true);

        try {
            Log::info("Extracting text from document for quiz generation", [
                'material_id' => $material->id,
                'question_count' => $questionCount
            ]);

            $pdfText = $this->pdfExtractor->extractText($material);
            $sourceCharCount = $this->pdfExtractor->getCharacterCount($pdfText);

            if ($sourceCharCount < 100) {
                throw new Exception('Insufficient text content for quiz generation (minimum 100 characters required)');
            }

            // Generate quiz using AI with automatic fallback on rate/token limits
            $activeProvider = $this->resolveAIProvider();
            Log::info("Generating quiz using {$activeProvider->getProviderName()} ({$activeProvider->getModelName()})");

            try {
                $quizData = $activeProvider->generateQuiz($pdfText, $material->title, $questionCount, $optionsPerQuestion);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $fallback = $this->resolveFallbackProvider($activeProvider->getProviderName());

                if ($fallback) {
                    Log::warning("Primary AI provider [{$activeProvider->getProviderName()}] failed ({$msg}). Automatically falling back to [{$fallback->getProviderName()}].");
                    $activeProvider = $fallback;
                    $quizData = $activeProvider->generateQuiz($pdfText, $material->title, $questionCount, $optionsPerQuestion);
                } else {
                    throw $e;
                }
            }

            // Validate quiz data
            $this->validateQuizData($quizData, $questionCount, $optionsPerQuestion);

            $generationTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            Log::info("Generated quiz with {$questionCount} questions in {$generationTimeMs}ms");

            // Save to database
            return DB::transaction(function () use (
                $material,
                $user,
                $quizData,
                $questionCount,
                $sourceCharCount,
                $generationTimeMs,
                $activeProvider
            ) {
                // If a quiz already exists for this material, update it as the canonical master quiz
                $existingQuiz = GeneratedQuiz::where('course_material_id', $material->id)->first();
                if ($existingQuiz) {
                    $existingQuiz->update([
                        'material_version' => $material->version,
                        'generated_by' => $user->id,
                        'ai_provider' => $activeProvider->getProviderName(),
                        'ai_model' => $activeProvider->getModelName(),
                        'question_count' => count($quizData['questions']),
                        'source_char_count' => $sourceCharCount,
                        'generation_time_ms' => $generationTimeMs,
                    ]);
                    $existingQuiz->questions()->delete();
                    $quiz = $existingQuiz;
                } else {
                    $quiz = GeneratedQuiz::create([
                        'course_material_id' => $material->id,
                        'material_version' => $material->version,
                        'generated_by' => $user->id,
                        'ai_provider' => $activeProvider->getProviderName(),
                        'ai_model' => $activeProvider->getModelName(),
                        'question_count' => count($quizData['questions']),
                        'source_char_count' => $sourceCharCount,
                        'generation_time_ms' => $generationTimeMs,
                    ]);
                }

                foreach ($quizData['questions'] as $index => $questionData) {
                    $question = QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_order' => $index + 1,
                        'question_text' => $questionData['question'],
                        'explanation' => $questionData['explanation'],
                    ]);

                    foreach ($questionData['options'] as $optionIndex => $optionText) {
                        QuizOption::create([
                            'question_id' => $question->id,
                            'option_order' => $optionIndex + 1,
                            'option_text' => $optionText,
                            'is_correct' => $optionIndex === (int) $questionData['correct_index'],
                        ]);
                    }
                }

                return $quiz->load(['questions.options']);
            });

        } catch (Exception $e) {
            Log::error("Quiz generation failed: {$e->getMessage()}", [
                'material_id' => $material->id,
                'user_id' => $user->id,
                'exception' => get_class($e),
            ]);

            throw $e;
        }
    }

    private function validateQuizData(array $quizData, int $expectedQuestionCount, int $expectedOptionsCount): void
    {
        if (!isset($quizData['questions']) || !is_array($quizData['questions'])) {
            throw new Exception('Invalid quiz data: missing questions array');
        }

        $actualQuestionCount = count($quizData['questions']);
        if ($actualQuestionCount < 1) {
            throw new Exception('No questions generated');
        }

        foreach ($quizData['questions'] as $index => $question) {
            $this->validateQuestion($question, $index, $expectedOptionsCount);
        }
    }

    private function validateQuestion(array $question, int $index, int $expectedOptionsCount): void
    {
        if (!isset($question['question']) || empty(trim($question['question']))) {
            throw new Exception("Question {$index} missing question text");
        }

        if (!isset($question['options']) || !is_array($question['options'])) {
            throw new Exception("Question {$index} missing options array");
        }

        $optionCount = count($question['options']);
        if ($optionCount < 2) {
            throw new Exception("Question {$index} has insufficient options (minimum 2 required)");
        }

        // Check for duplicate options
        $uniqueOptions = array_unique(array_map('trim', $question['options']));
        if (count($uniqueOptions) !== $optionCount) {
            throw new Exception("Question {$index} contains duplicate options");
        }

        // Check for empty options
        foreach ($question['options'] as $optionIndex => $option) {
            if (empty(trim($option))) {
                throw new Exception("Question {$index} has empty option at index {$optionIndex}");
            }
        }

        if (!isset($question['correct_index']) || !is_numeric($question['correct_index'])) {
            throw new Exception("Question {$index} missing or invalid correct_index");
        }

        $correctIndex = (int) $question['correct_index'];
        if ($correctIndex < 0 || $correctIndex >= $optionCount) {
            throw new Exception("Question {$index} has invalid correct_index: {$correctIndex}");
        }

        if (!isset($question['explanation']) || empty(trim($question['explanation']))) {
            throw new Exception("Question {$index} missing explanation");
        }
    }

    public function getUserQuizzes(User $user, int $perPage = 20)
    {
        $query = GeneratedQuiz::with(['courseMaterial.courseOffering.course', 'courseMaterial.courseOffering.department'])
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

    public function getUserQuiz(int $quizId, User $user): ?GeneratedQuiz
    {
        $query = GeneratedQuiz::where('id', $quizId)
            ->with([
                'courseMaterial.courseOffering.course',
                'courseMaterial.courseOffering.department',
                'questions.options'
            ]);

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

    public function deleteQuiz(GeneratedQuiz $quiz, User $user): bool
    {
        if (!$user->isAdmin() && $quiz->generated_by !== $user->id) {
            throw new Exception('Unauthorized to delete this quiz');
        }

        // Clear study task references before deleting
        \App\Models\StudyTask::where('generated_quiz_id', $quiz->id)
            ->update(['generated_quiz_id' => null]);

        // Clean up polymorphic bookmarks
        \App\Models\Bookmark::where('bookmarkable_type', GeneratedQuiz::class)
            ->where('bookmarkable_id', $quiz->id)
            ->delete();

        return $quiz->delete();
    }
}

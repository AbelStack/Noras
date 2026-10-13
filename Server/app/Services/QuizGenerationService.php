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
            throw new Exception("AI provider '{$providerName}' is not properly configured. Please check your environment variables.");
        }

        return $provider;
    }

    public function generateQuiz(CourseMaterial $material, User $user, int $questionCount = 5, int $optionsPerQuestion = 4): GeneratedQuiz
    {
        // Validate inputs
        $questionCount = max(1, min($questionCount, 20)); // Clamp between 1 and 20
        $optionsPerQuestion = max(2, min($optionsPerQuestion, 6)); // Clamp between 2 and 6

        $startTime = microtime(true);

        try {
            Log::info("Extracting text from PDF for quiz generation", [
                'material_id' => $material->id,
                'question_count' => $questionCount
            ]);

            $pdfText = $this->pdfExtractor->extractText($material);
            $sourceCharCount = $this->pdfExtractor->getCharacterCount($pdfText);

            if ($sourceCharCount < 100) {
                throw new Exception('Insufficient text content for quiz generation (minimum 100 characters required)');
            }

            Log::info("Generating quiz using {$this->aiProvider->getProviderName()} ({$this->aiProvider->getModelName()})");
            
            $quizData = $this->aiProvider->generateQuiz($pdfText, $material->title, $questionCount, $optionsPerQuestion);

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
                $generationTimeMs
            ) {
                $quiz = GeneratedQuiz::create([
                    'course_material_id' => $material->id,
                    'material_version' => $material->version,
                    'generated_by' => $user->id,
                    'ai_provider' => $this->aiProvider->getProviderName(),
                    'ai_model' => $this->aiProvider->getModelName(),
                    'question_count' => $questionCount,
                    'source_char_count' => $sourceCharCount,
                    'generation_time_ms' => $generationTimeMs,
                ]);

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
                            'is_correct' => $optionIndex === $questionData['correct_index'],
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

        if (!isset($question['correct_index'])) {
            throw new Exception("Question {$index} missing correct_index");
        }

        $correctIndex = $question['correct_index'];
        if (!is_int($correctIndex) || $correctIndex < 0 || $correctIndex >= $optionCount) {
            throw new Exception("Question {$index} has invalid correct_index: {$correctIndex}");
        }

        if (!isset($question['explanation']) || empty(trim($question['explanation']))) {
            throw new Exception("Question {$index} missing explanation");
        }
    }

    public function getUserQuizzes(User $user, int $perPage = 20)
    {
        return GeneratedQuiz::where('generated_by', $user->id)
            ->with(['courseMaterial.courseOffering.course', 'courseMaterial.courseOffering.department'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getUserQuiz(int $quizId, User $user): ?GeneratedQuiz
    {
        return GeneratedQuiz::where('id', $quizId)
            ->where('generated_by', $user->id)
            ->with([
                'courseMaterial.courseOffering.course',
                'courseMaterial.courseOffering.department',
                'questions.options'
            ])
            ->first();
    }

    public function deleteQuiz(GeneratedQuiz $quiz, User $user): bool
    {
        if ($quiz->generated_by !== $user->id) {
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

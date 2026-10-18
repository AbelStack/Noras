<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\GeneratedQuiz;
use App\Models\QuizAttempt;
use App\Services\QuizGenerationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneratedQuizController extends Controller
{
    private QuizGenerationService $quizService;

    public function __construct(QuizGenerationService $quizService)
    {
        $this->quizService = $quizService;
    }

    /**
     * Generate quiz from course material.
     * 
     * POST /api/v1/quizzes/generate/{materialId}
     */
    public function generate(Request $request, int $materialId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'question_count' => 'integer|min:1|max:20',
                'options_per_question' => 'integer|min:2|max:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $material = CourseMaterial::with(['courseOffering.course', 'courseOffering.department'])
                ->where('status', 'published')
                ->findOrFail($materialId);

            if (!$request->user()->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account must be approved to generate quizzes',
                ], 403);
            }

            $questionCount = $request->input('question_count', 5);
            $optionsPerQuestion = $request->input('options_per_question', 4);

            $quiz = $this->quizService->generateQuiz($material, $request->user(), $questionCount, $optionsPerQuestion);

            return response()->json([
                'success' => true,
                'message' => 'Quiz generated successfully',
                'data' => [
                    'quiz' => [
                        'id' => $quiz->id,
                        'question_count' => $quiz->question_count,
                        'ai_provider' => $quiz->ai_provider,
                        'ai_model' => $quiz->ai_model,
                        'source_char_count' => $quiz->source_char_count,
                        'generation_time_ms' => $quiz->generation_time_ms,
                        'created_at' => $quiz->created_at->toISOString(),
                    ],
                    'material' => [
                        'id' => $material->id,
                        'title' => $material->title,
                        'version' => $material->version,
                        'course' => [
                            'code' => $material->courseOffering->course->code,
                            'name' => $material->courseOffering->course->name,
                        ],
                        'department' => [
                            'code' => $material->courseOffering->department->code,
                            'name' => $material->courseOffering->department->name,
                        ],
                    ],
                ],
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate quiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all quizzes for authenticated user.
     * 
     * GET /api/v1/quizzes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 20);
            $perPage = min(max($perPage, 1), 100);

            $quizzes = $this->quizService->getUserQuizzes($request->user(), $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'quizzes' => $quizzes->getCollection()->map(function ($quiz) {
                        return [
                            'id' => $quiz->id,
                            'question_count' => $quiz->question_count,
                            'ai_provider' => $quiz->ai_provider,
                            'ai_model' => $quiz->ai_model,
                            'generation_time_ms' => $quiz->generation_time_ms,
                            'created_at' => $quiz->created_at->toISOString(),
                            'material' => $quiz->courseMaterial ? [
                                'id' => $quiz->courseMaterial->id,
                                'title' => $quiz->courseMaterial->title,
                                'version' => $quiz->courseMaterial->version,
                                'study_year' => $quiz->courseMaterial->courseOffering?->study_year,
                                'semester' => $quiz->courseMaterial->courseOffering?->semester,
                                'academic_year' => $quiz->courseMaterial->courseOffering?->academic_year,
                                'course' => $quiz->courseMaterial->courseOffering?->course ? [
                                    'code' => $quiz->courseMaterial->courseOffering->course->code,
                                    'title' => $quiz->courseMaterial->courseOffering->course->title ?? $quiz->courseMaterial->courseOffering->course->name,
                                    'name' => $quiz->courseMaterial->courseOffering->course->title ?? $quiz->courseMaterial->courseOffering->course->name,
                                ] : null,
                                'department' => $quiz->courseMaterial->courseOffering?->department ? [
                                    'code' => $quiz->courseMaterial->courseOffering->department->code,
                                    'name' => $quiz->courseMaterial->courseOffering->department->name,
                                ] : null,
                            ] : null,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $quizzes->currentPage(),
                        'per_page' => $quizzes->perPage(),
                        'total' => $quizzes->total(),
                        'last_page' => $quizzes->lastPage(),
                        'from' => $quizzes->firstItem(),
                        'to' => $quizzes->lastItem(),
                    ],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quizzes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific quiz with questions.
     * 
     * GET /api/v1/quizzes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $quiz = $this->quizService->getUserQuiz($id, $request->user());

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz' => [
                        'id' => $quiz->id,
                        'question_count' => $quiz->question_count,
                        'ai_provider' => $quiz->ai_provider,
                        'ai_model' => $quiz->ai_model,
                        'source_char_count' => $quiz->source_char_count,
                        'generation_time_ms' => $quiz->generation_time_ms,
                        'created_at' => $quiz->created_at->toISOString(),
                    ],
                    'material' => $quiz->courseMaterial ? [
                        'id' => $quiz->courseMaterial->id,
                        'title' => $quiz->courseMaterial->title,
                        'version' => $quiz->courseMaterial->version,
                        'study_year' => $quiz->courseMaterial->courseOffering?->study_year,
                        'semester' => $quiz->courseMaterial->courseOffering?->semester,
                        'academic_year' => $quiz->courseMaterial->courseOffering?->academic_year,
                        'course' => $quiz->courseMaterial->courseOffering?->course ? [
                            'code' => $quiz->courseMaterial->courseOffering->course->code,
                            'title' => $quiz->courseMaterial->courseOffering->course->title ?? $quiz->courseMaterial->courseOffering->course->name,
                            'name' => $quiz->courseMaterial->courseOffering->course->title ?? $quiz->courseMaterial->courseOffering->course->name,
                        ] : null,
                        'department' => $quiz->courseMaterial->courseOffering?->department ? [
                            'code' => $quiz->courseMaterial->courseOffering->department->code,
                            'name' => $quiz->courseMaterial->courseOffering->department->name,
                        ] : null,
                    ] : null,
                    'latest_attempt' => QuizAttempt::where('user_id', $request->user()->id)
                        ->where('quiz_id', $quiz->id)
                        ->latest()
                        ->first(),
                    'questions' => $quiz->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question_order' => $question->question_order,
                            'question_text' => $question->question_text,
                            'explanation' => $question->explanation,
                            'options' => $question->options->map(function ($option) {
                                return [
                                    'id' => $option->id,
                                    'option_order' => $option->option_order,
                                    'option_text' => $option->option_text,
                                    'is_correct' => $option->is_correct,
                                ];
                            }),
                        ];
                    }),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete quiz.
     * 
     * DELETE /api/v1/quizzes/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $quiz = GeneratedQuiz::find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found',
                ], 404);
            }

            $this->quizService->deleteQuiz($quiz, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz: ' . $e->getMessage(),
            ], $e->getMessage() === 'Unauthorized to delete this quiz' ? 403 : 500);
        }
    }

    /**
     * Submit quiz answers, calculate score, record attempt, and return review.
     * 
     * POST /api/v1/quizzes/{id}/submit
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'answers' => 'nullable|array',
            ]);

            $quiz = GeneratedQuiz::with(['questions.options', 'courseMaterial.courseOffering.course'])
                ->findOrFail($id);

            $userAnswers = $validated['answers'] ?? [];
            $totalQuestions = $quiz->questions->count();
            $score = 0;
            $questionResults = [];

            foreach ($quiz->questions->sortBy('question_order') as $question) {
                $correctOption = $question->options->firstWhere('is_correct', true);
                $selectedOptionId = $userAnswers[$question->id] ?? null;
                $selectedOption = $selectedOptionId ? $question->options->firstWhere('id', (int) $selectedOptionId) : null;
                $isCorrect = $selectedOption && $correctOption && ($selectedOption->id === $correctOption->id);

                if ($isCorrect) {
                    $score++;
                }

                $questionResults[] = [
                    'question_id' => $question->id,
                    'question_order' => $question->question_order,
                    'question_text' => $question->question_text,
                    'explanation' => $question->explanation,
                    'selected_option_id' => $selectedOptionId,
                    'selected_option_text' => $selectedOption?->option_text,
                    'correct_option_id' => $correctOption?->id,
                    'correct_option_text' => $correctOption?->option_text,
                    'is_correct' => (bool) $isCorrect,
                    'options' => $question->options->map(fn($opt) => [
                        'id' => $opt->id,
                        'option_order' => $opt->option_order,
                        'option_text' => $opt->option_text,
                        'is_correct' => (bool) $opt->is_correct,
                    ])->values(),
                ];
            }

            $percentage = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100, 2) : 0;

            $attempt = QuizAttempt::create([
                'user_id' => $request->user()->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'percentage' => $percentage,
                'answers' => $userAnswers,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz submitted successfully',
                'data' => [
                    'attempt_id' => $attempt->id,
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'percentage' => $percentage,
                    'created_at' => $attempt->created_at->toISOString(),
                    'question_results' => $questionResults,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's past attempts for this quiz.
     * 
     * GET /api/v1/quizzes/{id}/attempts
     */
    public function attempts(Request $request, int $id): JsonResponse
    {
        try {
            $attempts = QuizAttempt::where('user_id', $request->user()->id)
                ->where('quiz_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'attempts' => $attempts,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz attempts: ' . $e->getMessage(),
            ], 500);
        }
    }
}


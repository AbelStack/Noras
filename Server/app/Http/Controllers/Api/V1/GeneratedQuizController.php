<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\GeneratedQuiz;
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
                            'material' => [
                                'id' => $quiz->courseMaterial->id,
                                'title' => $quiz->courseMaterial->title,
                                'version' => $quiz->courseMaterial->version,
                                'course' => [
                                    'code' => $quiz->courseMaterial->courseOffering->course->code,
                                    'name' => $quiz->courseMaterial->courseOffering->course->name,
                                ],
                                'department' => [
                                    'code' => $quiz->courseMaterial->courseOffering->department->code,
                                    'name' => $quiz->courseMaterial->courseOffering->department->name,
                                ],
                            ],
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
                    'material' => [
                        'id' => $quiz->courseMaterial->id,
                        'title' => $quiz->courseMaterial->title,
                        'version' => $quiz->courseMaterial->version,
                    ],
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
}

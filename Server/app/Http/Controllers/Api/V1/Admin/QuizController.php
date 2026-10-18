<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\GeneratedQuiz;
use App\Services\QuizGenerationService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    protected QuizGenerationService $quizService;

    public function __construct(QuizGenerationService $quizService)
    {
        $this->quizService = $quizService;
    }

    /**
     * List all AI generated quizzes with curriculum filters and search
     */
    public function index(Request $request)
    {
        $query = GeneratedQuiz::select([
            'id',
            'course_material_id',
            'material_version',
            'generated_by',
            'ai_provider',
            'ai_model',
            'question_count',
            'source_char_count',
            'generation_time_ms',
            'created_at',
            'updated_at',
        ])->with([
            'courseMaterial:id,course_offering_id,title,original_filename,status',
            'courseMaterial.courseOffering:id,course_id,department_id,study_year,semester',
            'courseMaterial.courseOffering.course:id,code,title',
            'courseMaterial.courseOffering.department:id,university_id,name',
            'courseMaterial.courseOffering.department.university:id,name',
            'generatedBy:id,name,email',
        ]);

        // Filter by course material
        if ($request->filled('course_material_id')) {
            $query->where('course_material_id', $request->course_material_id);
        }

        // Filtering through course offering curriculum relationships
        if ($request->filled('university_id') || $request->filled('campus_id') || $request->filled('department_id') || $request->filled('study_year') || $request->filled('semester') || $request->filled('course_id')) {
            $universityId = $request->university_id ?: $request->campus_id;

            $query->whereHas('courseMaterial.courseOffering', function ($offeringQuery) use ($request, $universityId) {
                if ($request->filled('course_id')) {
                    $offeringQuery->where('course_id', $request->course_id);
                }
                if ($request->filled('department_id')) {
                    $offeringQuery->where('department_id', $request->department_id);
                }
                if ($request->filled('study_year')) {
                    $offeringQuery->where('study_year', $request->study_year);
                }
                if ($request->filled('semester')) {
                    $offeringQuery->where('semester', $request->semester);
                }
                if ($universityId) {
                    $offeringQuery->whereHas('department', function ($deptQuery) use ($universityId) {
                        $deptQuery->where('university_id', $universityId);
                    });
                }
            });
        }

        // Search in title, course code/title, or material filename
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('courseMaterial', function ($mq) use ($search) {
                    $mq->where('title', 'like', "%{$search}%")
                       ->orWhere('original_filename', 'like', "%{$search}%");
                })->orWhereHas('courseMaterial.courseOffering.course', function ($cq) use ($search) {
                    $cq->where('code', 'like', "%{$search}%")
                       ->orWhere('title', 'like', "%{$search}%");
                });
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $quizzes = $query->latest()->paginate($perPage);

        // Overall stats for admin dashboard widgets in a single optimized query
        $statsRow = GeneratedQuiz::selectRaw('COUNT(*) as total_quizzes, COALESCE(SUM(question_count), 0) as total_questions, COUNT(DISTINCT course_material_id) as unique_materials')->first();
        $stats = [
            'total_quizzes' => (int) ($statsRow->total_quizzes ?? 0),
            'total_questions' => (int) ($statsRow->total_questions ?? 0),
            'unique_materials' => (int) ($statsRow->unique_materials ?? 0),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'quizzes' => $quizzes->items(),
                'pagination' => [
                    'current_page' => $quizzes->currentPage(),
                    'last_page' => $quizzes->lastPage(),
                    'per_page' => $quizzes->perPage(),
                    'total' => $quizzes->total(),
                ],
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Show a single AI quiz with full questions, choices, explanations, and citations
     */
    public function show($id)
    {
        $quiz = GeneratedQuiz::with([
            'courseMaterial.courseOffering.course',
            'courseMaterial.courseOffering.department.university',
            'generatedBy:id,name,email',
            'questions' => function ($q) {
                $q->orderBy('question_order');
            },
            'questions.options' => function ($q) {
                $q->orderBy('option_order');
            },
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'quiz' => $quiz,
            ],
        ]);
    }

    /**
     * Generate or regenerate a quiz for a material
     */
    public function generate(Request $request, $materialId)
    {
        $material = CourseMaterial::findOrFail($materialId);

        $validated = $request->validate([
            'question_count' => ['nullable', 'integer', 'min:15', 'max:30'],
            'options_per_question' => ['nullable', 'integer', 'min:2', 'max:5'],
        ]);

        $questionCount = $validated['question_count'] ?? 20;
        $optionsCount = $validated['options_per_question'] ?? 4;

        try {
            $quiz = $this->quizService->generateQuiz($material, $request->user(), $questionCount, $optionsCount);

            $quiz->load([
                'courseMaterial.courseOffering.course',
                'courseMaterial.courseOffering.department.university',
                'generatedBy:id,name,email',
                'questions.options',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Official quiz generated successfully',
                'data' => [
                    'quiz' => $quiz,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate quiz: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an AI generated quiz
     */
    public function destroy(Request $request, $id)
    {
        $quiz = GeneratedQuiz::findOrFail($id);

        try {
            $this->quizService->deleteQuiz($quiz, $request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Quiz deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete quiz: ' . $e->getMessage(),
            ], 500);
        }
    }
}

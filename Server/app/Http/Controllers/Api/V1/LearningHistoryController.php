<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\QuizAttempt;
use App\Models\StudyTask;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningHistoryController extends Controller
{
    /**
     * Get unified learning history (notes and quizzes combined).
     *
     * Uses a database-level UNION ALL query with server-side pagination
     * instead of loading all records into memory.
     */
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;
        $userId = auth()->id();

        // Build efficient UNION ALL query selecting only metadata columns
        $notesQuery = DB::table('generated_notes')
            ->select([
                'id',
                DB::raw("'note' as type"),
                'course_material_id',
                'created_at',
                'updated_at',
            ])
            ->where('generated_by', $userId);

        $quizzesQuery = DB::table('generated_quizzes')
            ->select([
                'id',
                DB::raw("'quiz' as type"),
                'course_material_id',
                'created_at',
                'updated_at',
            ])
            ->where('generated_by', $userId);

        // Get total count efficiently
        $totalNotes = GeneratedNote::where('generated_by', $userId)->count();
        $totalQuizzes = GeneratedQuiz::where('generated_by', $userId)->count();
        $total = $totalNotes + $totalQuizzes;
        $lastPage = max(1, (int) ceil($total / $perPage));

        // Union, sort, and paginate at database level
        $offset = ($page - 1) * $perPage;
        $items = $notesQuery->unionAll($quizzesQuery)
            ->orderByDesc('created_at')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Eagerly load related course materials for only the paginated items
        $materialIds = $items->pluck('course_material_id')->filter()->unique()->values();
        $materials = [];
        if ($materialIds->isNotEmpty()) {
            $materials = DB::table('course_materials')
                ->whereIn('id', $materialIds)
                ->select(['id', 'title', 'mime_type'])
                ->get()
                ->keyBy('id');
        }

        // Format the response
        $data = $items->map(function ($item) use ($materials) {
            $material = $item->course_material_id ? ($materials[$item->course_material_id] ?? null) : null;

            return [
                'id' => $item->id,
                'type' => $item->type,
                'course_material_id' => $item->course_material_id,
                'course_material' => $material ? [
                    'id' => $material->id,
                    'title' => $material->title,
                    'mime_type' => $material->mime_type,
                ] : null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ]);
    }

    /**
     * Get learning progress summary.
     */
    public function progress(): JsonResponse
    {
        $userId = auth()->id();

        // Combine active/completed task counts into a single query
        $taskCounts = StudyTask::where('user_id', $userId)
            ->select([
                DB::raw('SUM(CASE WHEN completed = 0 THEN 1 ELSE 0 END) as active_tasks'),
                DB::raw('SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_tasks'),
            ])
            ->first();

        $notesGenerated = GeneratedNote::where('generated_by', $userId)->count();
        $quizzesGenerated = GeneratedQuiz::where('generated_by', $userId)->count();
        $quizzesCompleted = QuizAttempt::where('user_id', $userId)->count();
        $averageQuizScore = QuizAttempt::where('user_id', $userId)->avg('percentage');
        $bookmarksCount = Bookmark::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notes_generated' => $notesGenerated,
                'quizzes_generated' => $quizzesGenerated,
                'quizzes_completed' => $quizzesCompleted,
                'average_quiz_score' => $averageQuizScore ? round($averageQuizScore, 1) : 0,
                'active_tasks' => (int) ($taskCounts->active_tasks ?? 0),
                'completed_tasks' => (int) ($taskCounts->completed_tasks ?? 0),
                'bookmarks' => $bookmarksCount,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\StudyTask;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningHistoryController extends Controller
{
    /**
     * Get unified learning history (notes and quizzes combined).
     */
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $userId = auth()->id();

        // Fetch notes
        $notes = GeneratedNote::where('generated_by', $userId)
            ->with('courseMaterial')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'type' => 'note',
                    'course_material_id' => $note->course_material_id,
                    'course_material' => $note->courseMaterial ? [
                        'id' => $note->courseMaterial->id,
                        'title' => $note->courseMaterial->title,
                        'type' => $note->courseMaterial->type,
                    ] : null,
                    'created_at' => $note->created_at,
                    'updated_at' => $note->updated_at,
                ];
            });

        // Fetch quizzes
        $quizzes = GeneratedQuiz::where('generated_by', $userId)
            ->with('courseMaterial')
            ->get()
            ->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'type' => 'quiz',
                    'course_material_id' => $quiz->course_material_id,
                    'course_material' => $quiz->courseMaterial ? [
                        'id' => $quiz->courseMaterial->id,
                        'title' => $quiz->courseMaterial->title,
                        'type' => $quiz->courseMaterial->type,
                    ] : null,
                    'created_at' => $quiz->created_at,
                    'updated_at' => $quiz->updated_at,
                ];
            });

        // Merge and sort by created_at (newest first)
        $history = $notes->concat($quizzes)
            ->sortByDesc('created_at')
            ->values();

        // Manual pagination
        $total = $history->count();
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $history->slice($offset, $perPage)->values();
        $lastPage = (int) ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginatedItems,
            'pagination' => [
                'current_page' => $page,
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

        $notesGenerated = GeneratedNote::where('generated_by', $userId)->count();
        $quizzesGenerated = GeneratedQuiz::where('generated_by', $userId)->count();
        $activeTasks = StudyTask::where('user_id', $userId)
            ->where('completed', false)
            ->count();
        $completedTasks = StudyTask::where('user_id', $userId)
            ->where('completed', true)
            ->count();
        $bookmarksCount = Bookmark::where('user_id', $userId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notes_generated' => $notesGenerated,
                'quizzes_generated' => $quizzesGenerated,
                'active_tasks' => $activeTasks,
                'completed_tasks' => $completedTasks,
                'bookmarks' => $bookmarksCount,
            ],
        ]);
    }
}

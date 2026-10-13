<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudyTask;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudyTaskController extends Controller
{
    /**
     * Display a listing of the authenticated user's study tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'completed' => 'sometimes|in:true,false,1,0',
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $query = StudyTask::where('user_id', auth()->id())
            ->with(['courseMaterial', 'generatedNote', 'generatedQuiz'])
            ->latest();

        if (isset($validated['completed'])) {
            $completedValue = in_array($validated['completed'], ['true', '1'], true);
            $query->where('completed', $completedValue);
        }

        $tasks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created study task.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'nullable|date|after_or_equal:today',
            'course_material_id' => 'nullable|integer|exists:course_materials,id',
            'generated_note_id' => 'nullable|integer|exists:generated_notes,id',
            'generated_quiz_id' => 'nullable|integer|exists:generated_quizzes,id',
        ]);

        // Verify ownership of linked resources
        if (isset($validated['generated_note_id'])) {
            $note = GeneratedNote::find($validated['generated_note_id']);
            if ($note->generated_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only link your own notes to tasks.',
                ], 403);
            }
        }

        if (isset($validated['generated_quiz_id'])) {
            $quiz = GeneratedQuiz::find($validated['generated_quiz_id']);
            if ($quiz->generated_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only link your own quizzes to tasks.',
                ], 403);
            }
        }

        // For course material, verify it's published
        if (isset($validated['course_material_id'])) {
            $material = CourseMaterial::find($validated['course_material_id']);
            if ($material->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only link published materials to tasks.',
                ], 403);
            }
        }

        $task = StudyTask::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        $task->load(['courseMaterial', 'generatedNote', 'generatedQuiz']);

        Log::info('Study task created', [
            'user_id' => auth()->id(),
            'task_id' => $task->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Study task created successfully.',
            'data' => $task,
        ], 201);
    }

    /**
     * Display the specified study task.
     */
    public function show(string $id): JsonResponse
    {
        $task = StudyTask::with(['courseMaterial', 'generatedNote', 'generatedQuiz'])->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Study task not found.',
            ], 404);
        }

        // Verify ownership
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your own tasks.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    /**
     * Update the specified study task.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $task = StudyTask::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Study task not found.',
            ], 404);
        }

        // Verify ownership
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own tasks.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'nullable|date|after_or_equal:today',
            'course_material_id' => 'nullable|integer|exists:course_materials,id',
            'generated_note_id' => 'nullable|integer|exists:generated_notes,id',
            'generated_quiz_id' => 'nullable|integer|exists:generated_quizzes,id',
        ]);

        // Verify ownership of linked resources
        if (isset($validated['generated_note_id'])) {
            $note = GeneratedNote::find($validated['generated_note_id']);
            if ($note->generated_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only link your own notes to tasks.',
                ], 403);
            }
        }

        if (isset($validated['generated_quiz_id'])) {
            $quiz = GeneratedQuiz::find($validated['generated_quiz_id']);
            if ($quiz->generated_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only link your own quizzes to tasks.',
                ], 403);
            }
        }

        // For course material, verify it's published
        if (isset($validated['course_material_id'])) {
            $material = CourseMaterial::find($validated['course_material_id']);
            if ($material->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only link published materials to tasks.',
                ], 403);
            }
        }

        $task->update($validated);
        $task->load(['courseMaterial', 'generatedNote', 'generatedQuiz']);

        Log::info('Study task updated', [
            'user_id' => auth()->id(),
            'task_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Study task updated successfully.',
            'data' => $task,
        ]);
    }

    /**
     * Remove the specified study task.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = StudyTask::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Study task not found.',
            ], 404);
        }

        // Verify ownership
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own tasks.',
            ], 403);
        }

        $task->delete();

        Log::info('Study task deleted', [
            'user_id' => auth()->id(),
            'task_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Study task deleted successfully.',
        ]);
    }

    /**
     * Mark a study task as completed.
     */
    public function complete(string $id): JsonResponse
    {
        $task = StudyTask::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Study task not found.',
            ], 404);
        }

        // Verify ownership
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only complete your own tasks.',
            ], 403);
        }

        if ($task->completed) {
            return response()->json([
                'success' => false,
                'message' => 'Task is already completed.',
            ], 400);
        }

        $task->update([
            'completed' => true,
            'completed_at' => now(),
        ]);

        $task->load(['courseMaterial', 'generatedNote', 'generatedQuiz']);

        Log::info('Study task marked as completed', [
            'user_id' => auth()->id(),
            'task_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task marked as completed.',
            'data' => $task,
        ]);
    }

    /**
     * Reopen a completed study task.
     */
    public function reopen(string $id): JsonResponse
    {
        $task = StudyTask::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Study task not found.',
            ], 404);
        }

        // Verify ownership
        if ($task->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only reopen your own tasks.',
            ], 403);
        }

        if (!$task->completed) {
            return response()->json([
                'success' => false,
                'message' => 'Task is not completed.',
            ], 400);
        }

        $task->update([
            'completed' => false,
            'completed_at' => null,
        ]);

        $task->load(['courseMaterial', 'generatedNote', 'generatedQuiz']);

        Log::info('Study task reopened', [
            'user_id' => auth()->id(),
            'task_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task reopened.',
            'data' => $task,
        ]);
    }
}

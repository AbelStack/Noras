<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Services\NoteGenerationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GeneratedNoteController extends Controller
{
    private NoteGenerationService $noteService;

    public function __construct(NoteGenerationService $noteService)
    {
        $this->noteService = $noteService;
    }

    /**
     * Generate notes from a course material.
     * 
     * POST /api/v1/notes/generate/{materialId}
     * 
     * @param Request $request
     * @param int $materialId
     * @return JsonResponse
     */
    public function generate(Request $request, int $materialId): JsonResponse
    {
        try {
            // Find the material
            $material = CourseMaterial::with(['courseOffering.course', 'courseOffering.department'])
                ->where('status', 'published')
                ->findOrFail($materialId);

            // Authorization: user must be approved
            // TODO: Add enrollment check when university/department enrollment is implemented
            if (!$request->user()->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account must be approved to generate notes',
                ], 403);
            }

            // Check if regenerate flag is set
            $regenerate = $request->boolean('regenerate', false);

            // Generate notes
            $note = $this->noteService->generateNotes($material, $request->user(), $regenerate);

            return response()->json([
                'success' => true,
                'message' => 'Notes generated successfully',
                'data' => [
                    'note' => [
                        'id' => $note->id,
                        'content' => $note->content,
                        'ai_provider' => $note->ai_provider,
                        'ai_model' => $note->ai_model,
                        'source_char_count' => $note->source_char_count,
                        'notes_char_count' => $note->notes_char_count,
                        'generation_time_ms' => $note->generation_time_ms,
                        'created_at' => $note->created_at->toISOString(),
                        'updated_at' => $note->updated_at->toISOString(),
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
            ], $regenerate ? 200 : 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate notes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all notes for the authenticated user.
     * 
     * GET /api/v1/notes
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 20);
            $perPage = min(max($perPage, 1), 100); // Clamp between 1 and 100

            $notes = $this->noteService->getUserNotes($request->user(), $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'notes' => $notes->items()->map(function ($note) {
                        return [
                            'id' => $note->id,
                            'content' => $note->content,
                            'ai_provider' => $note->ai_provider,
                            'ai_model' => $note->ai_model,
                            'source_char_count' => $note->source_char_count,
                            'notes_char_count' => $note->notes_char_count,
                            'generation_time_ms' => $note->generation_time_ms,
                            'created_at' => $note->created_at->toISOString(),
                            'material' => [
                                'id' => $note->courseMaterial->id,
                                'title' => $note->courseMaterial->title,
                                'version' => $note->courseMaterial->version,
                                'course' => [
                                    'code' => $note->courseMaterial->courseOffering->course->code,
                                    'name' => $note->courseMaterial->courseOffering->course->name,
                                ],
                                'department' => [
                                    'code' => $note->courseMaterial->courseOffering->department->code,
                                    'name' => $note->courseMaterial->courseOffering->department->name,
                                ],
                            ],
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $notes->currentPage(),
                        'per_page' => $notes->perPage(),
                        'total' => $notes->total(),
                        'last_page' => $notes->lastPage(),
                        'from' => $notes->firstItem(),
                        'to' => $notes->lastItem(),
                    ],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific note.
     * 
     * GET /api/v1/notes/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $note = $this->noteService->getUserNote($id, $request->user());

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'note' => [
                        'id' => $note->id,
                        'content' => $note->content,
                        'ai_provider' => $note->ai_provider,
                        'ai_model' => $note->ai_model,
                        'source_char_count' => $note->source_char_count,
                        'notes_char_count' => $note->notes_char_count,
                        'generation_time_ms' => $note->generation_time_ms,
                        'created_at' => $note->created_at->toISOString(),
                        'updated_at' => $note->updated_at->toISOString(),
                    ],
                    'material' => [
                        'id' => $note->courseMaterial->id,
                        'title' => $note->courseMaterial->title,
                        'version' => $note->courseMaterial->version,
                        'course' => [
                            'code' => $note->courseMaterial->courseOffering->course->code,
                            'name' => $note->courseMaterial->courseOffering->course->name,
                        ],
                        'department' => [
                            'code' => $note->courseMaterial->courseOffering->department->code,
                            'name' => $note->courseMaterial->courseOffering->department->name,
                        ],
                    ],
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve note: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a generated note.
     * 
     * DELETE /api/v1/notes/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $note = GeneratedNote::find($id);

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found',
                ], 404);
            }

            $this->noteService->deleteNote($note, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete note: ' . $e->getMessage(),
            ], $e->getMessage() === 'Unauthorized to delete this note' ? 403 : 500);
        }
    }
}

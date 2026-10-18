<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Services\NoteGenerationService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    protected NoteGenerationService $noteService;

    public function __construct(NoteGenerationService $noteService)
    {
        $this->noteService = $noteService;
    }

    /**
     * List all AI generated notes with curriculum filters and search
     */
    public function index(Request $request)
    {
        $query = GeneratedNote::with([
            'courseMaterial.courseOffering.course',
            'courseMaterial.courseOffering.department.university',
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
        $notes = $query->latest()->paginate($perPage);

        // Overall stats for admin dashboard widgets
        $stats = [
            'total_notes' => GeneratedNote::count(),
            'total_characters' => (int) GeneratedNote::sum('notes_char_count'),
            'unique_materials' => GeneratedNote::distinct('course_material_id')->count('course_material_id'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'notes' => $notes->items(),
                'pagination' => [
                    'current_page' => $notes->currentPage(),
                    'last_page' => $notes->lastPage(),
                    'per_page' => $notes->perPage(),
                    'total' => $notes->total(),
                ],
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Show a single AI note with material & curriculum details
     */
    public function show($id)
    {
        $note = GeneratedNote::with([
            'courseMaterial.courseOffering.course',
            'courseMaterial.courseOffering.department.university',
            'generatedBy:id,name,email',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'note' => $note,
            ],
        ]);
    }

    /**
     * Generate or regenerate notes for a material
     */
    public function generate(Request $request, $materialId)
    {
        $material = CourseMaterial::findOrFail($materialId);

        try {
            $regenerate = $request->boolean('regenerate', true);
            $note = $this->noteService->generateNotes($material, $request->user(), $regenerate);

            $note->load([
                'courseMaterial.courseOffering.course',
                'courseMaterial.courseOffering.department.university',
                'generatedBy:id,name,email',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Official study note generated successfully',
                'data' => [
                    'note' => $note,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate note: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an AI generated note
     */
    public function destroy(Request $request, $id)
    {
        $note = GeneratedNote::findOrFail($id);

        try {
            $this->noteService->deleteNote($note, $request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Study note deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete note: ' . $e->getMessage(),
            ], 500);
        }
    }
}

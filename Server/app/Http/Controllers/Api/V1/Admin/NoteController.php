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
        $query = GeneratedNote::select([
            'id',
            'course_material_id',
            'material_version',
            'generated_by',
            'ai_provider',
            'ai_model',
            'source_char_count',
            'notes_char_count',
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
        $notes = $query->latest()->paginate($perPage);

        // Overall stats for admin dashboard widgets in a single optimized query
        $statsRow = GeneratedNote::selectRaw('COUNT(*) as total_notes, COALESCE(SUM(notes_char_count), 0) as total_characters, COUNT(DISTINCT course_material_id) as unique_materials')->first();
        $totalMaterials = CourseMaterial::count();
        $uniqueMaterials = (int) ($statsRow->unique_materials ?? 0);
        $pendingMaterials = max(0, $totalMaterials - $uniqueMaterials);
        $departmentsCovered = CourseMaterial::whereHas('masterNote')
            ->join('course_offerings', 'course_materials.course_offering_id', '=', 'course_offerings.id')
            ->distinct('course_offerings.department_id')
            ->count('course_offerings.department_id');

        $stats = [
            'total_notes' => (int) ($statsRow->total_notes ?? 0),
            'unique_materials' => $uniqueMaterials,
            'pending_materials' => $pendingMaterials,
            'departments_covered' => (int) $departmentsCovered,
            'total_characters' => (int) ($statsRow->total_characters ?? 0),
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
                'message' => $this->formatAiErrorMessage($e, 'study notes'),
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

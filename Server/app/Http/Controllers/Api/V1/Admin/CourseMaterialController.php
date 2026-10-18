<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use App\Services\CourseMaterialStorageService;
use App\Services\NoteGenerationService;
use App\Services\QuizGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseMaterialController extends Controller
{
    protected CourseMaterialStorageService $storageService;
    protected NoteGenerationService $noteService;
    protected QuizGenerationService $quizService;

    public function __construct(
        CourseMaterialStorageService $storageService,
        NoteGenerationService $noteService,
        QuizGenerationService $quizService
    ) {
        $this->storageService = $storageService;
        $this->noteService = $noteService;
        $this->quizService = $quizService;
    }

    public function index(Request $request)
    {
        $query = CourseMaterial::with([
            'courseOffering.course',
            'courseOffering.department.university',
            'uploader:id,name,email',
            'masterNote',
            'masterQuiz.questions.options',
        ]);

        if ($request->filled('course_offering_id')) {
            $query->where('course_offering_id', $request->course_offering_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('uploaded_by')) {
            $query->where('uploaded_by', $request->uploaded_by);
        }

        if ($request->filled('file_type')) {
            $type = strtolower($request->file_type);
            if ($type === 'pdf') {
                $query->where(function ($q) {
                    $q->where('mime_type', 'like', '%pdf%')
                      ->orWhere('original_filename', 'like', '%.pdf');
                });
            } elseif ($type === 'ppt' || $type === 'pptx') {
                $query->where(function ($q) {
                    $q->where('mime_type', 'like', '%powerpoint%')
                      ->orWhere('mime_type', 'like', '%presentation%')
                      ->orWhere('original_filename', 'like', '%.ppt')
                      ->orWhere('original_filename', 'like', '%.pptx');
                });
            }
        }

        // Filtering through relationship: course, department, university, study_year, semester
        if ($request->filled('course_id') || $request->filled('department_id') || $request->filled('university_id') || $request->filled('study_year') || $request->filled('semester')) {
            $query->whereHas('courseOffering', function ($offeringQuery) use ($request) {
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
                if ($request->filled('university_id')) {
                    $offeringQuery->whereHas('department', function ($deptQuery) use ($request) {
                        $deptQuery->where('university_id', $request->university_id);
                    });
                }
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('courseOffering.course', function ($cq) use ($search) {
                      $cq->where('code', 'like', "%{$search}%")
                         ->orWhere('title', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('courseOffering.department', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 50);
        $materials = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'materials' => $materials->items(),
                'pagination' => [
                    'current_page' => $materials->currentPage(),
                    'per_page' => $materials->perPage(),
                    'total' => $materials->total(),
                    'last_page' => $materials->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        @set_time_limit(300);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'in:draft,published'],
            'file' => ['required', 'file', 'max:' . (CourseMaterialStorageService::MAX_FILE_SIZE / 1024)],
            'course_offering_id' => ['nullable', 'exists:course_offerings,id'],
            'course_id' => ['required_without:course_offering_id', 'nullable', 'exists:courses,id'],
            'department_id' => ['required_without:course_offering_id', 'nullable', 'exists:departments,id'],
            'study_year' => ['required_without:course_offering_id', 'nullable', 'integer', 'min:1', 'max:8'],
            'semester' => ['required_without:course_offering_id', 'nullable', 'in:1,2,both'],
            'academic_year' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'auto_generate' => ['nullable', 'boolean'],
            'quiz_question_count' => ['nullable', 'integer', 'min:15', 'max:30'],
        ]);

        $file = $request->file('file');

        // Validate file signature and extension (PDF and PPT/PPTX)
        $fileInfo = $this->storageService->validateAndIdentify($file);
        if (!$fileInfo) {
            $msg = in_array(strtolower($file->getClientOriginalExtension()), ['ppt', 'pptx'])
                ? 'The uploaded file is not a valid PowerPoint presentation'
                : 'The uploaded file is not a valid PDF';
            return response()->json([
                'status' => 'error',
                'message' => $msg,
                'errors' => ['file' => [$msg]],
            ], 422);
        }

        // Resolve or create CourseOffering
        if ($request->filled('course_offering_id')) {
            $courseOffering = CourseOffering::find($validated['course_offering_id']);
        } else {
            $semesterVal = $request->semester === 'both' ? '1' : (string) $request->semester;
            $courseOffering = CourseOffering::firstOrCreate([
                'course_id' => $request->course_id,
                'department_id' => $request->department_id,
                'study_year' => $request->study_year,
                'semester' => $semesterVal,
            ], [
                'academic_year' => $request->academic_year ?? 2026,
                'is_active' => true,
            ]);
        }

        if (!$courseOffering) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not link course offering.',
                'errors' => ['course_offering_id' => ['Course offering could not be determined']],
            ], 422);
        }

        // Store file first - writes to Cloudflare R2 and local disk
        try {
            $stored = $this->storageService->storeMaterialFile($file, $fileInfo['extension']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store file: ' . $e->getMessage(),
            ], 500);
        }

        DB::beginTransaction();
        try {
            $material = CourseMaterial::create([
                'course_offering_id' => $courseOffering->id,
                'uploaded_by' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $stored['stored_filename'],
                'file_path' => $stored['file_path'],
                'file_size' => $stored['file_size'],
                'mime_type' => $fileInfo['mime_type'],
                'status' => $request->input('status', 'draft'),
                'version' => 1,
            ]);

            DB::commit();

            if ($request->boolean('auto_generate')) {
                $qCount = (int) $request->input('quiz_question_count', 20);
                $qCount = max(15, min(30, $qCount));

                try {
                    $this->noteService->generateNotes($material, auth()->user(), true);
                } catch (\Throwable $e) {
                    Log::warning("Auto note generation on upload failed: " . $e->getMessage());
                }

                try {
                    $this->quizService->generateQuiz($material, auth()->user(), $qCount, 4);
                } catch (\Throwable $e) {
                    Log::warning("Auto quiz generation on upload failed: " . $e->getMessage());
                }
            }

            $material->load([
                'courseOffering.course',
                'courseOffering.department.university',
                'uploader:id,name,email',
                'masterNote',
                'masterQuiz.questions.options',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Course material uploaded successfully',
                'data' => ['material' => $material],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up file if database failed
            $this->storageService->deleteFile($stored['file_path']);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save material record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $material = CourseMaterial::with([
            'courseOffering.course',
            'courseOffering.department.university',
            'uploader:id,name,email',
            'masterNote',
            'masterQuiz.questions.options',
            'replaces'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['material' => $material],
        ]);
    }

    public function generateNote(Request $request, $id)
    {
        @set_time_limit(300);
        $material = CourseMaterial::findOrFail($id);

        try {
            $regenerate = $request->boolean('regenerate', true);
            $note = $this->noteService->generateNotes($material, $request->user(), $regenerate);

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

    public function generateQuiz(Request $request, $id)
    {
        @set_time_limit(300);
        $material = CourseMaterial::findOrFail($id);

        $validated = $request->validate([
            'question_count' => ['nullable', 'integer', 'min:15', 'max:30'],
            'options_per_question' => ['nullable', 'integer', 'min:2', 'max:5'],
        ]);

        $questionCount = $validated['question_count'] ?? 20;
        $optionsCount = $validated['options_per_question'] ?? 4;

        try {
            $quiz = $this->quizService->generateQuiz($material, $request->user(), $questionCount, $optionsCount);

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

    public function download($id)
    {
        $material = CourseMaterial::findOrFail($id);
        $response = $this->storageService->download($material);

        if (!$response) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course material file not found on storage server',
            ], 404);
        }

        return $response;
    }

    public function update(Request $request, $id)
    {
        $material = CourseMaterial::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $material->update($validated);
        $material->load([
            'courseOffering.course',
            'courseOffering.department.university',
            'uploader:id,name,email'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Course material updated successfully',
            'data' => ['material' => $material],
        ]);
    }

    public function publish($id)
    {
        $material = CourseMaterial::findOrFail($id);

        if ($material->isPublished()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Material is already published',
            ], 400);
        }

        $material->update(['status' => 'published']);
        $material->load([
            'courseOffering.course',
            'courseOffering.department.university',
            'uploader:id,name,email'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Material published successfully',
            'data' => ['material' => $material],
        ]);
    }

    public function unpublish($id)
    {
        $material = CourseMaterial::findOrFail($id);

        if ($material->isDraft()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Material is already a draft',
            ], 400);
        }

        $material->update(['status' => 'draft']);
        $material->load([
            'courseOffering.course',
            'courseOffering.department.university',
            'uploader:id,name,email'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Material unpublished successfully',
            'data' => ['material' => $material],
        ]);
    }

    public function replace(Request $request, $id)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:' . (CourseMaterialStorageService::MAX_FILE_SIZE / 1024)],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('file');
        $fileInfo = $this->storageService->validateAndIdentify($file);
        if (!$fileInfo) {
            $msg = in_array(strtolower($file->getClientOriginalExtension()), ['ppt', 'pptx'])
                ? 'The uploaded file is not a valid PowerPoint presentation'
                : 'The uploaded file is not a valid PDF';
            return response()->json([
                'status' => 'error',
                'message' => $msg,
                'errors' => ['file' => [$msg]],
            ], 422);
        }

        try {
            $stored = $this->storageService->storeMaterialFile($file, $fileInfo['extension']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store replacement file: ' . $e->getMessage(),
            ], 500);
        }

        DB::beginTransaction();
        try {
            $oldMaterial = CourseMaterial::where('id', $id)->lockForUpdate()->firstOrFail();

            $newMaterial = CourseMaterial::create([
                'course_offering_id' => $oldMaterial->course_offering_id,
                'uploaded_by' => auth()->id(),
                'title' => $validated['title'] ?? $oldMaterial->title,
                'description' => $validated['description'] ?? $oldMaterial->description,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $stored['stored_filename'],
                'file_path' => $stored['file_path'],
                'file_size' => $stored['file_size'],
                'mime_type' => $fileInfo['mime_type'],
                'status' => 'draft',
                'version' => $oldMaterial->version + 1,
                'replaces_material_id' => $oldMaterial->id,
            ]);

            $newMaterial->load([
                'courseOffering.course',
                'courseOffering.department.university',
                'uploader:id,name,email',
                'replaces'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Course material replaced successfully',
                'data' => ['material' => $newMaterial],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->storageService->deleteFile($stored['file_path']);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to replace material: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $material = CourseMaterial::findOrFail($id);

        DB::beginTransaction();
        try {
            $filePath = $material->file_path;

            // Clear study task references (set to null) before deleting
            \App\Models\StudyTask::where('course_material_id', $material->id)
                ->update(['course_material_id' => null]);

            // Clean up polymorphic bookmarks
            \App\Models\Bookmark::where('bookmarkable_type', CourseMaterial::class)
                ->where('bookmarkable_id', $material->id)
                ->delete();

            // Delete the material record
            $material->delete();

            // Delete file from R2 and local
            $this->storageService->deleteFile($filePath);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Course material deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete material: ' . $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseMaterialController extends Controller
{
    private const MAX_FILE_SIZE = 52428800; // 50MB in bytes
    private const ALLOWED_MIME_TYPES = ['application/pdf'];
    private const PDF_MAGIC_BYTES = '%PDF';

    public function index(Request $request)
    {
        $query = CourseMaterial::with(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email']);

        if ($request->has('course_offering_id')) {
            $query->where('course_offering_id', $request->course_offering_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('uploaded_by')) {
            $query->where('uploaded_by', $request->uploaded_by);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('created_at', 'desc')
            ->paginate(20);

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
        $validated = $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:' . (self::MAX_FILE_SIZE / 1024)], // max in KB
        ]);

        $file = $request->file('file');

        // Validate file signature
        if (!$this->isValidPdf($file)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid PDF file',
                'errors' => ['file' => ['The uploaded file is not a valid PDF']],
            ], 422);
        }

        // Validate course offering exists
        $courseOffering = CourseOffering::find($validated['course_offering_id']);
        if (!$courseOffering) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course offering not found',
                'errors' => ['course_offering_id' => ['The selected course offering does not exist']],
            ], 422);
        }

        // Generate unique filename before transaction
        $storedFilename = Str::uuid() . '.pdf';
        $filePath = 'course-materials/' . $storedFilename;

        // Store file first - fail fast before database transaction
        try {
            Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store file',
            ], 500);
        }

        // Now attempt database transaction
        DB::beginTransaction();
        try {
            $material = CourseMaterial::create([
                'course_offering_id' => $validated['course_offering_id'],
                'uploaded_by' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'draft',
                'version' => 1,
            ]);

            $material->load(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Course material uploaded successfully',
                'data' => ['material' => $material],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up file since database operation failed
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload material',
            ], 500);
        }
    }

    public function show($id)
    {
        $material = CourseMaterial::with(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email', 'replaces'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['material' => $material],
        ]);
    }

    public function update(Request $request, $id)
    {
        $material = CourseMaterial::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $material->update($validated);
        $material->load(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email']);

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
        $material->load(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email']);

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
        $material->load(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email']);

        return response()->json([
            'status' => 'success',
            'message' => 'Material unpublished successfully',
            'data' => ['material' => $material],
        ]);
    }

    public function replace(Request $request, $id)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:' . (self::MAX_FILE_SIZE / 1024)],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('file');

        // Validate file signature
        if (!$this->isValidPdf($file)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid PDF file',
                'errors' => ['file' => ['The uploaded file is not a valid PDF']],
            ], 422);
        }

        // Generate unique filename before transaction
        $storedFilename = Str::uuid() . '.pdf';
        $filePath = 'course-materials/' . $storedFilename;

        // Store file first - fail fast before database transaction
        try {
            Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store file',
            ], 500);
        }

        // Now attempt database transaction with row locking
        DB::beginTransaction();
        try {
            // Lock the old material row to prevent concurrent replacements
            $oldMaterial = CourseMaterial::where('id', $id)->lockForUpdate()->firstOrFail();

            $newMaterial = CourseMaterial::create([
                'course_offering_id' => $oldMaterial->course_offering_id,
                'uploaded_by' => auth()->id(),
                'title' => $validated['title'] ?? $oldMaterial->title,
                'description' => $validated['description'] ?? $oldMaterial->description,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'draft',
                'version' => $oldMaterial->version + 1,
                'replaces_material_id' => $oldMaterial->id,
            ]);

            $newMaterial->load(['courseOffering.course', 'courseOffering.department', 'uploader:id,name,email', 'replaces']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Course material replaced successfully. New version created as draft.',
                'data' => ['material' => $newMaterial],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up file since database operation failed
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to replace material',
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

            // Then delete the file
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

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

    /**
     * Validate PDF file signature by checking magic bytes.
     * 
     * SECURITY NOTE: This validation only verifies that the file begins with '%PDF' header.
     * It does NOT guarantee the file is safe, complete, or non-malicious. This check:
     * - Prevents upload of files renamed to .pdf that are not PDFs
     * - Does NOT detect embedded malware, exploits, or malformed PDFs
     * - Does NOT validate PDF structure beyond the first 4 bytes
     * 
     * For production use, consider:
     * - Malware scanning (ClamAV, VirusTotal API, or commercial solutions)
     * - PDF structure validation libraries
     * - Restricted admin review before publication
     * - Sandboxed rendering environments
     * - Content Security Policy for downloads
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool True if file begins with PDF magic bytes
     */
    private function isValidPdf($file): bool
    {
        try {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fread($handle, 4);
            fclose($handle);

            return strpos($header, self::PDF_MAGIC_BYTES) === 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}

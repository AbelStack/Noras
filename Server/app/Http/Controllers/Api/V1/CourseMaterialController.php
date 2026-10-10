<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseMaterial::with(['courseOffering.course', 'courseOffering.department'])
            ->where('status', 'published');

        if ($request->has('course_offering_id')) {
            $query->where('course_offering_id', $request->course_offering_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => ['materials' => $materials],
        ]);
    }

    public function show($id)
    {
        $material = CourseMaterial::with(['courseOffering.course', 'courseOffering.department'])
            ->where('status', 'published')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['material' => $material],
        ]);
    }

    public function download($id)
    {
        $material = CourseMaterial::where('status', 'published')->findOrFail($id);

        if (!Storage::disk('local')->exists($material->file_path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found',
            ], 404);
        }

        return Storage::disk('local')->download(
            $material->file_path,
            $material->original_filename,
            [
                'Content-Type' => $material->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $material->original_filename . '"',
            ]
        );
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Services\CourseMaterialStorageService;
use Illuminate\Http\Request;

class CourseMaterialController extends Controller
{
    protected CourseMaterialStorageService $storageService;

    public function __construct(CourseMaterialStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $query = CourseMaterial::with(['courseOffering.course', 'courseOffering.department.university'])
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

    public function show($id)
    {
        $material = CourseMaterial::with(['courseOffering.course', 'courseOffering.department.university'])
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
        $response = $this->storageService->download($material);

        if (!$response) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found',
            ], 404);
        }

        return $response;
    }
}

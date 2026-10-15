<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use Illuminate\Http\Request;

class CourseOfferingController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseOffering::with(['course', 'department.university']);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->has('study_year')) {
            $query->where('study_year', $request->study_year);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $offerings = $query->orderBy('academic_year', 'desc')
            ->orderBy('study_year')
            ->orderBy('semester')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'course_offerings' => $offerings->items(),
                'pagination' => [
                    'current_page' => $offerings->currentPage(),
                    'per_page' => $offerings->perPage(),
                    'total' => $offerings->total(),
                    'last_page' => $offerings->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'academic_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'study_year' => ['required', 'integer', 'min:1', 'max:6'],
            'semester' => ['required', 'in:1,2'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate offering
        $exists = CourseOffering::where('course_id', $validated['course_id'])
            ->where('department_id', $validated['department_id'])
            ->where('academic_year', $validated['academic_year'])
            ->where('study_year', $validated['study_year'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This course offering already exists',
                'errors' => ['course_id' => ['Course offering must be unique']],
            ], 422);
        }

        $offering = CourseOffering::create($validated);
        $offering->load(['course', 'department.university']);

        return response()->json([
            'status' => 'success',
            'message' => 'Course offering created successfully',
            'data' => ['course_offering' => $offering],
        ], 201);
    }

    public function show($id)
    {
        $offering = CourseOffering::with(['course', 'department.university'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['course_offering' => $offering],
        ]);
    }

    public function update(Request $request, $id)
    {
        $offering = CourseOffering::findOrFail($id);

        $validated = $request->validate([
            'course_id' => ['sometimes', 'exists:courses,id'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'academic_year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'study_year' => ['sometimes', 'integer', 'min:1', 'max:6'],
            'semester' => ['sometimes', 'in:1,2'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate if key fields are being changed
        if (isset($validated['course_id']) || isset($validated['department_id']) || 
            isset($validated['academic_year']) || isset($validated['study_year']) || 
            isset($validated['semester'])) {
            
            $exists = CourseOffering::where('course_id', $validated['course_id'] ?? $offering->course_id)
                ->where('department_id', $validated['department_id'] ?? $offering->department_id)
                ->where('academic_year', $validated['academic_year'] ?? $offering->academic_year)
                ->where('study_year', $validated['study_year'] ?? $offering->study_year)
                ->where('semester', $validated['semester'] ?? $offering->semester)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This course offering already exists',
                    'errors' => ['course_id' => ['Course offering must be unique']],
                ], 422);
            }
        }

        $offering->update($validated);
        $offering->load(['course', 'department.university']);

        return response()->json([
            'status' => 'success',
            'message' => 'Course offering updated successfully',
            'data' => ['course_offering' => $offering],
        ]);
    }

    public function destroy($id)
    {
        $offering = CourseOffering::findOrFail($id);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $materials = \App\Models\CourseMaterial::where('course_offering_id', $offering->id)->get();

            foreach ($materials as $material) {
                \App\Models\StudyTask::where('course_material_id', $material->id)
                    ->update(['course_material_id' => null]);

                \App\Models\Bookmark::where('bookmarkable_type', \App\Models\CourseMaterial::class)
                    ->where('bookmarkable_id', $material->id)
                    ->delete();

                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($material->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($material->file_path);
                }

                $material->delete();
            }

            $offering->delete();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Course offering deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete course offering: ' . $e->getMessage(),
            ], 500);
        }
    }
}

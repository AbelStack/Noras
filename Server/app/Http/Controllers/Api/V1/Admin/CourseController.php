<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\CourseMaterial;
use App\Models\StudyTask;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['offerings.department.university']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('department_id')) {
            $query->whereHas('offerings', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('code')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'courses' => $courses->items(),
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                    'last_page' => $courses->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'study_year' => ['nullable', 'integer', 'min:1', 'max:8'],
            'study_years' => ['nullable', 'array'],
            'study_years.*' => ['integer', 'min:1', 'max:8'],
            'semester' => ['nullable', 'in:1,2,both'],
            'academic_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $course = Course::create([
            'code' => $validated['code'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'credit_hours' => $validated['credit_hours'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['department_id'])) {
            $academicYear = $validated['academic_year'] ?? 2026;
            $semesterInput = $validated['semester'] ?? '1';
            $semesters = $semesterInput === 'both' ? ['1', '2'] : [(string)$semesterInput];

            $years = [];
            if (!empty($validated['study_years']) && is_array($validated['study_years'])) {
                $years = $validated['study_years'];
            } elseif (!empty($validated['study_year'])) {
                $years = [$validated['study_year']];
            }

            foreach ($years as $year) {
                foreach ($semesters as $sem) {
                    CourseOffering::firstOrCreate([
                        'course_id' => $course->id,
                        'department_id' => $validated['department_id'],
                        'academic_year' => $academicYear,
                        'study_year' => $year,
                        'semester' => (string)$sem,
                    ], [
                        'is_active' => $course->is_active,
                    ]);
                }
            }
        }

        $course->load(['offerings.department.university']);

        return response()->json([
            'status' => 'success',
            'message' => 'Course created successfully',
            'data' => ['course' => $course],
        ], 201);
    }

    public function show($id)
    {
        $course = Course::with(['offerings' => function($query) {
            $query->with(['department.university']);
        }])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['course' => $course],
        ]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:20', Rule::unique('courses')->ignore($id)],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credit_hours' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'study_year' => ['nullable', 'integer', 'min:1', 'max:8'],
            'study_years' => ['nullable', 'array'],
            'study_years.*' => ['integer', 'min:1', 'max:8'],
            'semester' => ['nullable', 'in:1,2,both'],
            'academic_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $course->update(collect($validated)->only(['code', 'title', 'description', 'credit_hours', 'is_active'])->toArray());

        if (array_key_exists('department_id', $validated) && !empty($validated['department_id'])) {
            $academicYear = $validated['academic_year'] ?? 2026;
            $semesterInput = $validated['semester'] ?? '1';
            $semesters = $semesterInput === 'both' ? ['1', '2'] : [(string)$semesterInput];

            $years = [];
            if (!empty($validated['study_years']) && is_array($validated['study_years'])) {
                $years = $validated['study_years'];
            } elseif (!empty($validated['study_year'])) {
                $years = [$validated['study_year']];
            }

            foreach ($years as $year) {
                foreach ($semesters as $sem) {
                    CourseOffering::firstOrCreate([
                        'course_id' => $course->id,
                        'department_id' => $validated['department_id'],
                        'academic_year' => $academicYear,
                        'study_year' => $year,
                        'semester' => (string)$sem,
                    ], [
                        'is_active' => $course->is_active,
                    ]);
                }
            }
        }

        $course->load(['offerings.department.university']);

        return response()->json([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'data' => ['course' => $course->fresh(['offerings.department.university'])],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        if ($course->is_active && !$request->boolean('force') && $course->offerings()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete course with existing offerings. Deactivate instead.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $offeringIds = $course->offerings()->pluck('id');

            if ($offeringIds->isNotEmpty()) {
                $materials = CourseMaterial::whereIn('course_offering_id', $offeringIds)->get();

                foreach ($materials as $material) {
                    // Nullify study tasks referencing this material
                    StudyTask::where('course_material_id', $material->id)
                        ->update(['course_material_id' => null]);

                    // Remove bookmarks for this material
                    Bookmark::where('bookmarkable_type', CourseMaterial::class)
                        ->where('bookmarkable_id', $material->id)
                        ->delete();

                    // Remove file if exists
                    if (Storage::disk('local')->exists($material->file_path)) {
                        Storage::disk('local')->delete($material->file_path);
                    }

                    $material->delete();
                }

                $course->offerings()->delete();
            }

            $course->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Course deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete course: ' . $e->getMessage(),
            ], 500);
        }
    }
}

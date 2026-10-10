<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
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
        ]);

        $course = Course::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Course created successfully',
            'data' => ['course' => $course],
        ], 201);
    }

    public function show($id)
    {
        $course = Course::with(['offerings' => function($query) {
            $query->with(['department.university'])->where('is_active', true);
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
        ]);

        $course->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Course updated successfully',
            'data' => ['course' => $course->fresh()],
        ]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        if ($course->offerings()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete course with existing offerings. Deactivate instead.',
            ], 400);
        }

        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Course deleted successfully',
        ]);
    }
}

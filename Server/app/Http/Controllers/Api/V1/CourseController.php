<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseOffering;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::where('is_active', true);

        if ($request->has('department_id')) {
            $query->whereHas('activeOfferings', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->has('study_year')) {
            $query->whereHas('activeOfferings', function($q) use ($request) {
                $q->where('study_year', $request->study_year);
            });
        }

        if ($request->has('semester')) {
            $query->whereHas('activeOfferings', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $courses = $query->orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data' => ['courses' => $courses],
        ]);
    }

    public function show($id)
    {
        $course = Course::where('is_active', true)
            ->with(['activeOfferings' => function($query) {
                $query->with(['department.university'])->orderBy('academic_year', 'desc');
            }])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['course' => $course],
        ]);
    }

    public function offerings(Request $request)
    {
        $query = CourseOffering::where('is_active', true)
            ->with(['course', 'department.university']);

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

        $offerings = $query->orderBy('study_year')
            ->orderBy('semester')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['course_offerings' => $offerings],
        ]);
    }
}

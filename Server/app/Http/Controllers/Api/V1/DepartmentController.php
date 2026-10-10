<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::where('is_active', true)->with('university');

        if ($request->has('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        $departments = $query->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => ['departments' => $departments],
        ]);
    }

    public function show($id)
    {
        $department = Department::where('is_active', true)
            ->with(['university', 'activeCourseOfferings.course'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['department' => $department],
        ]);
    }
}

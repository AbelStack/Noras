<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with('university');

        if ($request->has('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $departments = $query->orderBy('name')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'departments' => $departments->items(),
                'pagination' => [
                    'current_page' => $departments->currentPage(),
                    'per_page' => $departments->perPage(),
                    'total' => $departments->total(),
                    'last_page' => $departments->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'university_id' => ['required', 'exists:universities,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate code within the same university
        $exists = Department::where('university_id', $validated['university_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Department code already exists for this university',
                'errors' => ['code' => ['Department code must be unique within the university']],
            ], 422);
        }

        $department = Department::create($validated);
        $department->load('university');

        return response()->json([
            'status' => 'success',
            'message' => 'Department created successfully',
            'data' => ['department' => $department],
        ], 201);
    }

    public function show($id)
    {
        $department = Department::with('university')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['department' => $department],
        ]);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'university_id' => ['sometimes', 'exists:universities,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        // Check for duplicate code if code or university is being changed
        if (isset($validated['code']) || isset($validated['university_id'])) {
            $universityId = $validated['university_id'] ?? $department->university_id;
            $code = $validated['code'] ?? $department->code;

            $exists = Department::where('university_id', $universityId)
                ->where('code', $code)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Department code already exists for this university',
                    'errors' => ['code' => ['Department code must be unique within the university']],
                ], 422);
            }
        }

        $department->update($validated);
        $department->load('university');

        return response()->json([
            'status' => 'success',
            'message' => 'Department updated successfully',
            'data' => ['department' => $department],
        ]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->courseOfferings()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete department with existing course offerings. Deactivate instead.',
            ], 400);
        }

        $department->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Department deleted successfully',
        ]);
    }
}

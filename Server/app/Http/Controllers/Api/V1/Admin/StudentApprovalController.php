<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentApprovalController extends Controller
{
    public function approve(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        if ($student->account_status === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Student is already approved',
            ], 400);
        }

        $student->update([
            'account_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $student->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Student approved successfully',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'account_status' => $student->account_status,
                    'approved_at' => $student->approved_at?->toISOString(),
                ],
            ],
        ]);
    }

    public function reject(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $student->update([
            'account_status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $student->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Student rejected successfully',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'account_status' => $student->account_status,
                ],
            ],
        ]);
    }

    public function suspend(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $student->update([
            'account_status' => 'suspended',
        ]);

        $student->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Student suspended successfully',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'account_status' => $student->account_status,
                ],
            ],
        ]);
    }

    public function reactivate(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        if (!in_array($student->account_status, ['suspended', 'rejected'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only suspended or rejected accounts can be reactivated',
            ], 400);
        }

        $student->update([
            'account_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $student->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Student reactivated successfully',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'account_status' => $student->account_status,
                    'approved_at' => $student->approved_at?->toISOString(),
                ],
            ],
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected,suspended',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = min((int) $request->query('per_page', 15), 100);
        
        $query = User::where('role', 'student');
        
        if ($status) {
            $query->where('account_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'student_id' => $student->student_id,
                        'account_status' => $student->account_status,
                        'approved_at' => $student->approved_at?->toISOString(),
                        'created_at' => $student->created_at->toISOString(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                    'last_page' => $students->lastPage(),
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'student_id' => $student->student_id,
                    'role' => $student->role,
                    'account_status' => $student->account_status,
                    'approved_at' => $student->approved_at?->toISOString(),
                    'approved_by' => $student->approved_by,
                    'created_at' => $student->created_at->toISOString(),
                    'updated_at' => $student->updated_at->toISOString(),
                ],
            ],
        ]);
    }
}

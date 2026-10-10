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
        $status = $request->query('status');
        
        $query = User::where('role', 'student');
        
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'suspended'])) {
            $query->where('account_status', $status);
        }

        $students = $query->orderBy('created_at', 'desc')->get();

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
            ],
        ]);
    }
}

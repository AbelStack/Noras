<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\University;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Get active campuses for public registration
     */
    public function campuses()
    {
        $campuses = University::where('is_active', true)
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'campuses' => $campuses,
            ],
        ]);
    }

    /**
     * Get active departments for public registration (filterable by university_id)
     */
    public function departments(Request $request)
    {
        $query = Department::where('is_active', true)
            ->select(['id', 'university_id', 'name', 'code'])
            ->orderBy('name');

        if ($request->filled('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        $departments = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'departments' => $departments,
            ],
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'student_id' => $request->student_id,
            'university_id' => $request->university_id,
            'department_id' => $request->department_id,
            'role' => 'student',
            'account_status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful. Your account is pending approval.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                    'university_id' => $user->university_id,
                    'department_id' => $user->department_id,
                    'account_status' => $user->account_status,
                    'role' => $user->role,
                ],
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::with(['university', 'department'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->account_status !== 'approved') {
            $message = match ($user->account_status) {
                'pending' => 'Your account is pending approval',
                'rejected' => 'Your account has been rejected',
                'suspended' => 'Your account has been suspended',
                default => 'Account not accessible',
            };

            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                    'role' => $user->role,
                    'account_status' => $user->account_status,
                    'university_id' => $user->university_id,
                    'department_id' => $user->department_id,
                    'university' => $user->university ? [
                        'id' => $user->university->id,
                        'name' => $user->university->name,
                    ] : null,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name,
                        'code' => $user->department->code,
                    ] : null,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['university', 'department']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'student_id' => $user->student_id,
                    'role' => $user->role,
                    'account_status' => $user->account_status,
                    'university_id' => $user->university_id,
                    'department_id' => $user->department_id,
                    'university' => $user->university ? [
                        'id' => $user->university->id,
                        'name' => $user->university->name,
                    ] : null,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name,
                        'code' => $user->department->code,
                    ] : null,
                    'approved_at' => $user->approved_at?->toISOString(),
                ],
            ],
        ]);
    }
}

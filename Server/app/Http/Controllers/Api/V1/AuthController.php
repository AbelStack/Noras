<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'student_id' => $request->student_id,
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
                    'account_status' => $user->account_status,
                    'role' => $user->role,
                ],
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

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
                    'approved_at' => $user->approved_at?->toISOString(),
                ],
            ],
        ]);
    }
}

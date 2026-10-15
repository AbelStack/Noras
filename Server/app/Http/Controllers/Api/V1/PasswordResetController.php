<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given user.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always return the same response to prevent email enumeration
        // regardless of whether the email exists or not
        return response()->json([
            'status' => 'success',
            'message' => 'If an account exists with that email, a password reset link has been sent.',
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => 'Your password has been reset successfully. You can now log in with your new password.',
            ]);
        }

        // Handle different error scenarios
        $message = match ($status) {
            Password::INVALID_TOKEN => 'This password reset link is invalid or has expired. Please request a new one.',
            Password::INVALID_USER => 'We could not find a user with that email address.',
            Password::THROTTLED => 'Too many password reset attempts. Please try again later.',
            default => 'An error occurred while resetting your password. Please try again.',
        };

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 400);
    }
}

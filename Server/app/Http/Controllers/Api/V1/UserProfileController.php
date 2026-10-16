<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    /**
     * Upload or update profile picture (Max 2MB)
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
        ], [
            'avatar.required' => 'Please select a profile photo to upload.',
            'avatar.image' => 'The selected file must be a valid image (JPEG, PNG, JPG, WEBP, or GIF).',
            'avatar.mimes' => 'Allowed image formats are: JPEG, PNG, JPG, WEBP, and GIF.',
            'avatar.max' => 'The profile photo must not exceed 2MB in size.',
        ]);

        $user = $request->user();

        // Delete old avatar if present
        if ($user->avatar && Storage::disk('local')->exists($user->avatar)) {
            Storage::disk('local')->delete($user->avatar);
        }

        // Store new avatar in local disk under avatars folder
        $file = $request->file('avatar');
        $extension = $file->getClientOriginalExtension();
        $filename = 'avatar_' . $user->id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $path = $file->storeAs('avatars', $filename, 'local');

        $user->update(['avatar' => $path]);
        $user->load(['university', 'department']);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture updated successfully',
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
                    'avatar_url' => $user->avatar_url,
                ],
            ],
        ]);
    }

    /**
     * Remove the current profile picture
     */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('local')->exists($user->avatar)) {
            Storage::disk('local')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);
        $user->load(['university', 'department']);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture removed successfully',
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
                    'avatar_url' => null,
                ],
            ],
        ]);
    }

    /**
     * Stream avatar image file
     */
    public function getAvatar(Request $request, $filename)
    {
        // Prevent path traversal
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
            abort(404);
        }

        $path = "avatars/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Avatar not found');
        }

        $fullPath = Storage::disk('local')->path($path);
        $mimeType = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

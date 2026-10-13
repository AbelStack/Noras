<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the authenticated user's bookmarks.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'bookmarkable_type' => ['sometimes', Rule::in([CourseMaterial::class, GeneratedNote::class, GeneratedQuiz::class])],
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $query = Bookmark::where('user_id', auth()->id())
            ->with('bookmarkable')
            ->latest();

        if (isset($validated['bookmarkable_type'])) {
            $query->where('bookmarkable_type', $validated['bookmarkable_type']);
        }

        $bookmarks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $bookmarks->items(),
            'pagination' => [
                'current_page' => $bookmarks->currentPage(),
                'per_page' => $bookmarks->perPage(),
                'total' => $bookmarks->total(),
                'last_page' => $bookmarks->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created bookmark.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bookmarkable_type' => ['required', Rule::in([CourseMaterial::class, GeneratedNote::class, GeneratedQuiz::class])],
            'bookmarkable_id' => 'required|integer|min:1',
        ]);

        // Verify the resource exists and user has access
        $resourceClass = $validated['bookmarkable_type'];
        $resource = $resourceClass::find($validated['bookmarkable_id']);

        if (!$resource) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        }

        // Verify ownership for user-generated content
        if (in_array($resourceClass, [GeneratedNote::class, GeneratedQuiz::class])) {
            if ($resource->generated_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only bookmark your own notes and quizzes.',
                ], 403);
            }
        }

        // For CourseMaterial, verify it's published
        if ($resourceClass === CourseMaterial::class && $resource->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'You can only bookmark published materials.',
            ], 403);
        }

        // Check if bookmark already exists
        $existing = Bookmark::where('user_id', auth()->id())
            ->where('bookmarkable_type', $validated['bookmarkable_type'])
            ->where('bookmarkable_id', $validated['bookmarkable_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark already exists.',
            ], 409);
        }

        $bookmark = Bookmark::create([
            'user_id' => auth()->id(),
            'bookmarkable_type' => $validated['bookmarkable_type'],
            'bookmarkable_id' => $validated['bookmarkable_id'],
        ]);

        $bookmark->load('bookmarkable');

        Log::info('Bookmark created', [
            'user_id' => auth()->id(),
            'bookmark_id' => $bookmark->id,
            'bookmarkable_type' => $validated['bookmarkable_type'],
            'bookmarkable_id' => $validated['bookmarkable_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark created successfully.',
            'data' => $bookmark,
        ], 201);
    }

    /**
     * Display the specified bookmark.
     */
    public function show(string $id): JsonResponse
    {
        $bookmark = Bookmark::with('bookmarkable')->find($id);

        if (!$bookmark) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found.',
            ], 404);
        }

        // Verify ownership
        if ($bookmark->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your own bookmarks.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $bookmark,
        ]);
    }

    /**
     * Remove the specified bookmark.
     */
    public function destroy(string $id): JsonResponse
    {
        $bookmark = Bookmark::find($id);

        if (!$bookmark) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found.',
            ], 404);
        }

        // Verify ownership
        if ($bookmark->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own bookmarks.',
            ], 403);
        }

        $bookmark->delete();

        Log::info('Bookmark deleted', [
            'user_id' => auth()->id(),
            'bookmark_id' => $id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark deleted successfully.',
        ]);
    }
}

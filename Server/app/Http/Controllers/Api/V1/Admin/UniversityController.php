<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UniversityController extends Controller
{
    public function index()
    {
        $universities = University::withCount('departments')
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'universities' => $universities->items(),
                'pagination' => [
                    'current_page' => $universities->currentPage(),
                    'per_page' => $universities->perPage(),
                    'total' => $universities->total(),
                    'last_page' => $universities->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:universities,name'],
            'code' => ['required', 'string', 'max:20', 'unique:universities,code'],
            'is_active' => ['boolean'],
        ]);

        $university = University::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'University created successfully',
            'data' => ['university' => $university],
        ], 201);
    }

    public function show($id)
    {
        $university = University::withCount('departments')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['university' => $university],
        ]);
    }

    public function update(Request $request, $id)
    {
        $university = University::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('universities')->ignore($id)],
            'code' => ['sometimes', 'string', 'max:20', Rule::unique('universities')->ignore($id)],
            'is_active' => ['boolean'],
        ]);

        $university->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'University updated successfully',
            'data' => ['university' => $university->fresh()],
        ]);
    }

    public function destroy($id)
    {
        $university = University::findOrFail($id);

        if ($university->departments()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete university with existing departments. Deactivate instead.',
            ], 400);
        }

        $university->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'University deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\University;

class UniversityController extends Controller
{
    public function index()
    {
        $universities = University::where('is_active', true)
            ->withCount('activeDepartments')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['universities' => $universities],
        ]);
    }

    public function show($id)
    {
        $university = University::where('is_active', true)
            ->with(['activeDepartments' => function($query) {
                $query->orderBy('name');
            }])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => ['university' => $university],
        ]);
    }
}

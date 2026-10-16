<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\University;
use App\Models\Department;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use Illuminate\Support\Facades\Cache;

class PublicStatsController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('public_landing_stats', 60, function () {
            $materialsCount = CourseMaterial::where('status', 'published')->count();
            $studentsCount = User::where('role', 'student')->count();
            $coursesCount = Course::where('is_active', true)->count();
            $campusesCount = University::where('is_active', true)->count();
            $departmentsCount = Department::where('is_active', true)->count();
            $notesCount = GeneratedNote::count();
            $quizzesCount = GeneratedQuiz::count();

            $campuses = University::where('is_active', true)
                ->withCount(['departments' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->get(['id', 'name', 'code']);

            return [
                'materials' => $materialsCount,
                'students' => $studentsCount,
                'courses' => $coursesCount,
                'campuses' => $campusesCount,
                'departments' => $departmentsCount,
                'notes' => $notesCount,
                'quizzes' => $quizzesCount,
                'campuses_list' => $campuses,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }
}

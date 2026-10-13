<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\University;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\CourseMaterial;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary()
    {
        $studentCounts = User::where('role', 'student')
            ->select('account_status', DB::raw('count(*) as count'))
            ->groupBy('account_status')
            ->pluck('count', 'account_status')
            ->toArray();

        $data = [
            'students' => [
                'total' => User::where('role', 'student')->count(),
                'pending' => $studentCounts['pending'] ?? 0,
                'approved' => $studentCounts['approved'] ?? 0,
                'rejected' => $studentCounts['rejected'] ?? 0,
                'suspended' => $studentCounts['suspended'] ?? 0,
            ],
            'catalog' => [
                'universities' => University::count(),
                'departments' => Department::count(),
                'courses' => Course::count(),
                'course_offerings' => CourseOffering::count(),
            ],
            'materials' => [
                'total' => CourseMaterial::count(),
                'published' => CourseMaterial::where('status', 'published')->count(),
                'draft' => CourseMaterial::where('status', 'draft')->count(),
            ],
            'ai_generated' => [
                'notes' => GeneratedNote::count(),
                'quizzes' => GeneratedQuiz::count(),
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}

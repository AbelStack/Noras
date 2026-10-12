<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Admin\StudentApprovalController;
use App\Http\Controllers\Api\V1\Admin\UniversityController as AdminUniversityController;
use App\Http\Controllers\Api\V1\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Api\V1\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Api\V1\Admin\CourseOfferingController as AdminCourseOfferingController;
use App\Http\Controllers\Api\V1\Admin\CourseMaterialController as AdminCourseMaterialController;
use App\Http\Controllers\Api\V1\UniversityController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\CourseMaterialController;
use App\Http\Controllers\Api\V1\GeneratedNoteController;
use App\Http\Controllers\Api\V1\GeneratedQuizController;

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'index']);

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::middleware('approved')->group(function () {
            // Student read-only catalog access
            Route::get('/universities', [UniversityController::class, 'index']);
            Route::get('/universities/{id}', [UniversityController::class, 'show']);
            
            Route::get('/departments', [DepartmentController::class, 'index']);
            Route::get('/departments/{id}', [DepartmentController::class, 'show']);
            
            Route::get('/courses', [CourseController::class, 'index']);
            Route::get('/courses/{id}', [CourseController::class, 'show']);
            Route::get('/course-offerings', [CourseController::class, 'offerings']);

            // Student read-only material access
            Route::get('/materials', [CourseMaterialController::class, 'index']);
            Route::get('/materials/{id}', [CourseMaterialController::class, 'show']);
            Route::get('/materials/{id}/download', [CourseMaterialController::class, 'download']);

            // AI-generated notes (with rate limiting on generation)
            Route::post('/notes/generate/{materialId}', [GeneratedNoteController::class, 'generate'])
                ->middleware('throttle:note-generation');
            Route::get('/notes', [GeneratedNoteController::class, 'index']);
            Route::get('/notes/{id}', [GeneratedNoteController::class, 'show']);
            Route::delete('/notes/{id}', [GeneratedNoteController::class, 'destroy']);

            // AI-generated quizzes (with rate limiting on generation)
            Route::post('/quizzes/generate/{materialId}', [GeneratedQuizController::class, 'generate'])
                ->middleware('throttle:quiz-generation');
            Route::get('/quizzes', [GeneratedQuizController::class, 'index']);
            Route::get('/quizzes/{id}', [GeneratedQuizController::class, 'show']);
            Route::delete('/quizzes/{id}', [GeneratedQuizController::class, 'destroy']);
        });

        Route::middleware('admin')->prefix('admin')->group(function () {
            // Student approval
            Route::get('/students', [StudentApprovalController::class, 'index']);
            Route::post('/students/{id}/approve', [StudentApprovalController::class, 'approve']);
            Route::post('/students/{id}/reject', [StudentApprovalController::class, 'reject']);
            Route::post('/students/{id}/suspend', [StudentApprovalController::class, 'suspend']);
            Route::post('/students/{id}/reactivate', [StudentApprovalController::class, 'reactivate']);

            // Catalog management
            Route::apiResource('universities', AdminUniversityController::class);
            Route::apiResource('departments', AdminDepartmentController::class);
            Route::apiResource('courses', AdminCourseController::class);
            Route::apiResource('course-offerings', AdminCourseOfferingController::class);

            // Course material management
            Route::get('materials', [AdminCourseMaterialController::class, 'index']);
            Route::post('materials', [AdminCourseMaterialController::class, 'store']);
            Route::get('materials/{id}', [AdminCourseMaterialController::class, 'show']);
            Route::put('materials/{id}', [AdminCourseMaterialController::class, 'update']);
            Route::delete('materials/{id}', [AdminCourseMaterialController::class, 'destroy']);
            Route::post('materials/{id}/publish', [AdminCourseMaterialController::class, 'publish']);
            Route::post('materials/{id}/unpublish', [AdminCourseMaterialController::class, 'unpublish']);
            Route::post('materials/{id}/replace', [AdminCourseMaterialController::class, 'replace']);
        });
    });
});

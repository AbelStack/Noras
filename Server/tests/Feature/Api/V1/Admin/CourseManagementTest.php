<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
    }

    public function test_admin_can_create_course(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/courses', [
            'code' => 'CS101',
            'title' => 'Introduction to Programming',
            'description' => 'Basic programming concepts',
            'credit_hours' => 3,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('courses', ['code' => 'CS101']);
    }

    public function test_admin_can_create_course_offering(): void
    {
        $admin = $this->createAdmin();
        $course = Course::factory()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/course-offerings', [
            'course_id' => $course->id,
            'department_id' => $department->id,
            'academic_year' => 2026,
            'study_year' => 1,
            'semester' => '1',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('course_offerings', [
            'course_id' => $course->id,
            'department_id' => $department->id,
        ]);
    }

    public function test_duplicate_course_offering_rejected(): void
    {
        $admin = $this->createAdmin();
        $course = Course::factory()->create();
        $department = Department::factory()->create();

        CourseOffering::factory()->create([
            'course_id' => $course->id,
            'department_id' => $department->id,
            'academic_year' => 2026,
            'study_year' => 1,
            'semester' => '1',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/course-offerings', [
            'course_id' => $course->id,
            'department_id' => $department->id,
            'academic_year' => 2026,
            'study_year' => 1,
            'semester' => '1',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_deactivate_course(): void
    {
        $admin = $this->createAdmin();
        $course = Course::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/courses/{$course->id}", [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'is_active' => false]);
    }

    public function test_admin_cannot_delete_course_with_offerings(): void
    {
        $admin = $this->createAdmin();
        $course = Course::factory()->create();
        CourseOffering::factory()->create(['course_id' => $course->id]);

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/courses/{$course->id}");

        $response->assertStatus(400);
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_course_validation_rejects_invalid_credit_hours(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/courses', [
            'code' => 'CS101',
            'title' => 'Test Course',
            'credit_hours' => 15,
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_filter_course_offerings(): void
    {
        $admin = $this->createAdmin();
        $department = Department::factory()->create();
        CourseOffering::factory()->count(2)->create([
            'department_id' => $department->id,
            'study_year' => 1,
            'semester' => '1',
        ]);
        CourseOffering::factory()->create(['study_year' => 2]);

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/course-offerings?study_year=1&semester=1");

        $response->assertStatus(200);
    }
}

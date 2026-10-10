<?php

namespace Tests\Feature\Api\V1;

use App\Models\University;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCatalogAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedStudent()
    {
        return User::factory()->create(['role' => 'student', 'account_status' => 'approved']);
    }

    private function createPendingStudent()
    {
        return User::factory()->create(['role' => 'student', 'account_status' => 'pending']);
    }

    public function test_approved_student_can_view_universities(): void
    {
        $student = $this->createApprovedStudent();
        University::factory()->count(2)->create(['is_active' => true]);

        $response = $this->actingAs($student)->getJson('/api/v1/universities');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['universities']]);
    }

    public function test_approved_student_can_view_departments(): void
    {
        $student = $this->createApprovedStudent();
        Department::factory()->count(2)->create(['is_active' => true]);

        $response = $this->actingAs($student)->getJson('/api/v1/departments');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['departments']]);
    }

    public function test_approved_student_can_view_courses(): void
    {
        $student = $this->createApprovedStudent();
        Course::factory()->count(2)->create(['is_active' => true]);

        $response = $this->actingAs($student)->getJson('/api/v1/courses');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['courses']]);
    }

    public function test_approved_student_can_filter_courses_by_department(): void
    {
        $student = $this->createApprovedStudent();
        $department = Department::factory()->create();
        $course = Course::factory()->create();
        CourseOffering::factory()->create([
            'course_id' => $course->id,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($student)->getJson("/api/v1/courses?department_id={$department->id}");

        $response->assertStatus(200);
    }

    public function test_pending_student_cannot_access_catalog(): void
    {
        $student = $this->createPendingStudent();

        $response = $this->actingAs($student)->getJson('/api/v1/universities');

        $response->assertStatus(403);
    }

    public function test_student_only_sees_active_universities(): void
    {
        $student = $this->createApprovedStudent();
        University::factory()->create(['is_active' => true, 'name' => 'Active University']);
        University::factory()->create(['is_active' => false, 'name' => 'Inactive University']);

        $response = $this->actingAs($student)->getJson('/api/v1/universities');

        $response->assertStatus(200);
        $data = $response->json('data.universities');
        $this->assertCount(1, $data);
    }

    public function test_unauthenticated_user_cannot_access_catalog(): void
    {
        $response = $this->getJson('/api/v1/universities');
        $response->assertStatus(401);
    }

    public function test_student_can_search_courses(): void
    {
        $student = $this->createApprovedStudent();
        Course::factory()->create(['code' => 'CS101', 'title' => 'Intro to Programming']);
        Course::factory()->create(['code' => 'MATH201', 'title' => 'Calculus']);

        $response = $this->actingAs($student)->getJson('/api/v1/courses?search=CS101');

        $response->assertStatus(200);
    }
}

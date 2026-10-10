<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\University;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
    }

    public function test_admin_can_create_department(): void
    {
        $admin = $this->createAdmin();
        $university = University::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/departments', [
            'university_id' => $university->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('departments', ['name' => 'Computer Science', 'code' => 'CS']);
    }

    public function test_duplicate_department_code_within_university_rejected(): void
    {
        $admin = $this->createAdmin();
        $university = University::factory()->create();
        Department::factory()->create(['university_id' => $university->id, 'code' => 'CS']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/departments', [
            'university_id' => $university->id,
            'name' => 'Computer Science 2',
            'code' => 'CS',
        ]);

        $response->assertStatus(422);
    }

    public function test_same_department_code_allowed_in_different_universities(): void
    {
        $admin = $this->createAdmin();
        $university1 = University::factory()->create();
        $university2 = University::factory()->create();
        
        Department::factory()->create(['university_id' => $university1->id, 'code' => 'CS']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/departments', [
            'university_id' => $university2->id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $response->assertStatus(201);
    }

    public function test_admin_can_filter_departments_by_university(): void
    {
        $admin = $this->createAdmin();
        $university = University::factory()->create();
        Department::factory()->count(2)->create(['university_id' => $university->id]);
        Department::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/departments?university_id={$university->id}");

        $response->assertStatus(200);
    }

    public function test_invalid_university_id_rejected(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/departments', [
            'university_id' => 99999,
            'name' => 'Test Department',
            'code' => 'TD',
        ]);

        $response->assertStatus(422);
    }
}

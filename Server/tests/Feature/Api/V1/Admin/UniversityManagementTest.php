<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\University;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
    }

    private function createStudent()
    {
        return User::factory()->create(['role' => 'student', 'account_status' => 'approved']);
    }

    public function test_admin_can_create_university(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/universities', [
            'name' => 'Wollo University',
            'code' => 'WU',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'University created successfully',
            ]);

        $this->assertDatabaseHas('universities', [
            'name' => 'Wollo University',
            'code' => 'WU',
        ]);
    }

    public function test_admin_can_list_universities(): void
    {
        $admin = $this->createAdmin();
        University::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/universities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'universities',
                    'pagination',
                ],
            ]);
    }

    public function test_admin_can_update_university(): void
    {
        $admin = $this->createAdmin();
        $university = University::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/universities/{$university->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('universities', ['id' => $university->id, 'name' => 'New Name']);
    }

    public function test_admin_cannot_delete_university_with_departments(): void
    {
        $admin = $this->createAdmin();
        $university = University::factory()->create();
        Department::factory()->create(['university_id' => $university->id]);

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/universities/{$university->id}");

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    public function test_student_cannot_create_university(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->postJson('/api/v1/admin/universities', [
            'name' => 'Test University',
            'code' => 'TU',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_universities(): void
    {
        $response = $this->getJson('/api/v1/admin/universities');
        $response->assertStatus(401);
    }

    public function test_duplicate_university_code_rejected(): void
    {
        $admin = $this->createAdmin();
        University::factory()->create(['code' => 'WU']);

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/universities', [
            'name' => 'Another University',
            'code' => 'WU',
        ]);

        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\CourseMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentMaterialAccessTest extends TestCase
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

    private function createSuspendedStudent()
    {
        return User::factory()->create(['role' => 'student', 'account_status' => 'suspended']);
    }

    public function test_approved_student_can_view_published_materials(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        CourseMaterial::factory()->published()->count(2)->create();
        CourseMaterial::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($student)->getJson('/api/v1/materials');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.materials'));
    }

    public function test_approved_student_can_view_single_published_material(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        $material = CourseMaterial::factory()->published()->create();

        $response = $this->actingAs($student)->getJson("/api/v1/materials/{$material->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.material.id', $material->id);
    }

    public function test_student_cannot_view_draft_material(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($student)->getJson("/api/v1/materials/{$material->id}");

        $response->assertStatus(404);
    }

    public function test_approved_student_can_download_published_material(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        $material = CourseMaterial::factory()->published()->create();

        Storage::disk('local')->put($material->file_path, '%PDF-1.4 test content');

        $response = $this->actingAs($student)->get("/api/v1/materials/{$material->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_student_cannot_download_draft_material(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        Storage::disk('local')->put($material->file_path, 'test content');

        $response = $this->actingAs($student)->get("/api/v1/materials/{$material->id}/download");

        $response->assertStatus(404);
    }

    public function test_pending_student_cannot_access_materials(): void
    {
        Storage::fake('local');
        $student = $this->createPendingStudent();
        CourseMaterial::factory()->published()->create();

        $response = $this->actingAs($student)->getJson('/api/v1/materials');

        $response->assertStatus(403);
    }

    public function test_suspended_student_cannot_access_materials(): void
    {
        Storage::fake('local');
        $student = $this->createSuspendedStudent();
        CourseMaterial::factory()->published()->create();

        $response = $this->actingAs($student)->getJson('/api/v1/materials');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_materials(): void
    {
        Storage::fake('local');
        CourseMaterial::factory()->published()->create();

        $response = $this->getJson('/api/v1/materials');

        $response->assertStatus(401);
    }

    public function test_student_can_filter_materials_by_course_offering(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        $material1 = CourseMaterial::factory()->published()->create();
        CourseMaterial::factory()->published()->create();

        $response = $this->actingAs($student)->getJson("/api/v1/materials?course_offering_id={$material1->course_offering_id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.materials'));
    }

    public function test_student_can_search_materials(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        CourseMaterial::factory()->published()->create(['title' => 'Introduction to Programming']);
        CourseMaterial::factory()->published()->create(['title' => 'Advanced Calculus']);

        $response = $this->actingAs($student)->getJson('/api/v1/materials?search=Programming');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.materials'));
    }

    public function test_download_returns_404_if_file_missing(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        $material = CourseMaterial::factory()->published()->create();

        // Don't create the file

        $response = $this->actingAs($student)->get("/api/v1/materials/{$material->id}/download");

        $response->assertStatus(404)
            ->assertJsonPath('message', 'File not found');
    }

    public function test_approved_student_token_loses_access_after_suspension(): void
    {
        Storage::fake('local');
        $student = $this->createApprovedStudent();
        CourseMaterial::factory()->published()->create();

        // First request should succeed
        $response = $this->actingAs($student)->getJson('/api/v1/materials');
        $response->assertStatus(200);

        // Suspend the student
        $student->update(['account_status' => 'suspended']);

        // Second request with same token should fail
        $response = $this->actingAs($student)->getJson('/api/v1/materials');
        $response->assertStatus(403);
    }
}

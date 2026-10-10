<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use App\Models\CourseOffering;
use App\Models\CourseMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseMaterialManagementTest extends TestCase
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

    private function createValidPdf()
    {
        // Create a minimal valid PDF file
        $pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000056 00000 n\n0000000115 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n210\n%%EOF";
        return UploadedFile::fake()->createWithContent('test.pdf', $pdfContent);
    }

    public function test_admin_can_upload_pdf_material(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $offering = CourseOffering::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/materials', [
            'course_offering_id' => $offering->id,
            'title' => 'Week 1 Lecture Notes',
            'description' => 'Introduction to the course',
            'file' => $this->createValidPdf(),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'material' => [
                        'id', 'title', 'description', 'original_filename',
                        'file_size', 'mime_type', 'status', 'version'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('course_materials', [
            'course_offering_id' => $offering->id,
            'title' => 'Week 1 Lecture Notes',
            'status' => 'draft',
            'version' => 1,
        ]);

        Storage::disk('local')->assertExists('course-materials/' . CourseMaterial::first()->stored_filename);
    }

    public function test_admin_can_list_materials_with_filters(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $offering1 = CourseOffering::factory()->create();
        $offering2 = CourseOffering::factory()->create();

        CourseMaterial::factory()->create(['course_offering_id' => $offering1->id, 'status' => 'published']);
        CourseMaterial::factory()->create(['course_offering_id' => $offering2->id, 'status' => 'draft']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/materials?course_offering_id=' . $offering1->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.materials'));
    }

    public function test_admin_can_update_material_metadata(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->create(['uploaded_by' => $admin->id]);

        $response = $this->actingAs($admin)->putJson("/api/v1/admin/materials/{$material->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_materials', [
            'id' => $material->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    public function test_admin_can_publish_material(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/materials/{$material->id}/publish");

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_materials', [
            'id' => $material->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_unpublish_material(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->published()->create();

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/materials/{$material->id}/unpublish");

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_materials', [
            'id' => $material->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_replace_material_with_new_version(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->create(['version' => 1, 'uploaded_by' => $admin->id]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/materials/{$material->id}/replace", [
            'file' => $this->createValidPdf(),
        ]);

        $response->assertStatus(201);
        
        $newMaterial = CourseMaterial::where('replaces_material_id', $material->id)->first();
        $this->assertNotNull($newMaterial);
        $this->assertEquals(2, $newMaterial->version);
        $this->assertEquals('draft', $newMaterial->status);
        $this->assertEquals($material->course_offering_id, $newMaterial->course_offering_id);
    }

    public function test_admin_can_delete_material(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->create();

        Storage::disk('local')->put($material->file_path, 'test content');

        $response = $this->actingAs($admin)->deleteJson("/api/v1/admin/materials/{$material->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('course_materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($material->file_path);
    }

    public function test_invalid_file_type_rejected(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $offering = CourseOffering::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/materials', [
            'course_offering_id' => $offering->id,
            'title' => 'Invalid File',
            'file' => UploadedFile::fake()->create('document.txt', 100),
        ]);

        $response->assertStatus(422);
    }

    public function test_invalid_pdf_signature_rejected(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $offering = CourseOffering::factory()->create();

        $fakeFile = UploadedFile::fake()->createWithContent('fake.pdf', 'Not a real PDF file');

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/materials', [
            'course_offering_id' => $offering->id,
            'title' => 'Fake PDF',
            'file' => $fakeFile,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.file.0', 'The uploaded file is not a valid PDF');
    }

    public function test_invalid_course_offering_rejected(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/v1/admin/materials', [
            'course_offering_id' => 99999,
            'title' => 'Test Material',
            'file' => $this->createValidPdf(),
        ]);

        $response->assertStatus(422);
    }

    public function test_student_cannot_upload_material(): void
    {
        Storage::fake('local');
        $student = $this->createStudent();
        $offering = CourseOffering::factory()->create();

        $response = $this->actingAs($student)->postJson('/api/v1/admin/materials', [
            'course_offering_id' => $offering->id,
            'title' => 'Student Upload',
            'file' => $this->createValidPdf(),
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_upload_material(): void
    {
        Storage::fake('local');
        $offering = CourseOffering::factory()->create();

        $response = $this->postJson('/api/v1/admin/materials', [
            'course_offering_id' => $offering->id,
            'title' => 'Unauthorized Upload',
            'file' => $this->createValidPdf(),
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_publish_already_published_material(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->published()->create();

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/materials/{$material->id}/publish");

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Material is already published');
    }

    public function test_cannot_unpublish_draft_material(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();
        $material = CourseMaterial::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/materials/{$material->id}/unpublish");

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Material is already a draft');
    }
}

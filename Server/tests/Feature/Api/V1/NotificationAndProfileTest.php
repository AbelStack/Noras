<?php

namespace Tests\Feature\Api\V1;

use App\Models\AppNotification;
use App\Models\Department;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private University $university;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Kombolcha Campus',
            'code' => 'KOMB',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'university_id' => $this->university->id,
            'name' => 'Software Engineering',
            'code' => 'SE',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@university.edu',
            'student_id' => 'ADMIN001',
            'password' => 'password123',
            'role' => 'admin',
            'account_status' => 'approved',
        ]);

        $this->student = User::create([
            'name' => 'Test Student',
            'email' => 'student@university.edu',
            'student_id' => 'ETS1001/15',
            'password' => 'password123',
            'university_id' => $this->university->id,
            'department_id' => $this->department->id,
            'role' => 'student',
            'account_status' => 'approved',
        ]);
    }

    public function test_new_student_registration_notifies_admin(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Fresh Student',
            'email' => 'fresh@university.edu',
            'student_id' => 'ETS9999/16',
            'university_id' => $this->university->id,
            'department_id' => $this->department->id,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->admin->id,
            'type' => 'student_registered',
            'title' => 'New Student Registration',
        ]);

        $notification = AppNotification::where('user_id', $this->admin->id)->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Fresh Student', $notification->message);
        $this->assertNull($notification->read_at);
    }

    public function test_notifications_unread_count_and_badge_logic(): void
    {
        // Create 12 notifications for admin
        for ($i = 1; $i <= 12; $i++) {
            AppNotification::create([
                'user_id' => $this->admin->id,
                'type' => 'student_registered',
                'title' => "Registration $i",
                'message' => "Student $i registered",
                'read_at' => null,
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonPath('data.unread_count', 12)
            ->assertJsonPath('data.unread_badge', '9+');

        // Mark 3 as read so 9 remain
        $notifications = AppNotification::where('user_id', $this->admin->id)->take(3)->get();
        foreach ($notifications as $notif) {
            $this->actingAs($this->admin, 'sanctum')
                ->patchJson("/api/v1/notifications/{$notif->id}/read")
                ->assertStatus(200);
        }

        $checkResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/notifications');

        $checkResponse->assertStatus(200)
            ->assertJsonPath('data.unread_count', 9)
            ->assertJsonPath('data.unread_badge', '9');

        // Mark another one read, should count down to 8
        $next = AppNotification::where('user_id', $this->admin->id)->unread()->first();
        $markRes = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/notifications/{$next->id}/read");

        $markRes->assertStatus(200)
            ->assertJsonPath('data.unread_count', 8)
            ->assertJsonPath('data.unread_badge', '8');
    }

    public function test_mark_all_notifications_as_read(): void
    {
        AppNotification::create([
            'user_id' => $this->admin->id,
            'type' => 'student_registered',
            'title' => 'Reg 1',
            'message' => 'Student 1 registered',
            'read_at' => null,
        ]);
        AppNotification::create([
            'user_id' => $this->admin->id,
            'type' => 'student_registered',
            'title' => 'Reg 2',
            'message' => 'Student 2 registered',
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/notifications/mark-all-read');

        $response->assertStatus(200)
            ->assertJsonPath('data.unread_count', 0)
            ->assertJsonPath('data.unread_badge', '0');

        $this->assertEquals(0, AppNotification::where('user_id', $this->admin->id)->unread()->count());
    }

    public function test_user_can_upload_profile_picture_max_2mb(): void
    {
        Storage::fake('local');

        $gifBytes = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7') . str_repeat('A', 1500 * 1024);
        $image = UploadedFile::fake()->createWithContent('avatar.gif', $gifBytes);

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/v1/user/profile-picture', [
                'avatar' => $image,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->student->refresh();
        $this->assertNotNull($this->student->avatar);
        $this->assertNotNull($this->student->avatar_url);
        Storage::disk('local')->assertExists($this->student->avatar);
    }

    public function test_profile_picture_exceeding_2mb_is_rejected(): void
    {
        Storage::fake('local');

        $largeGifBytes = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7') . str_repeat('A', 2500 * 1024);
        $largeImage = UploadedFile::fake()->createWithContent('large.gif', $largeGifBytes);

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/v1/user/profile-picture', [
                'avatar' => $largeImage,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_user_can_remove_profile_picture(): void
    {
        Storage::fake('local');

        $gifBytes = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $image = UploadedFile::fake()->createWithContent('temp.gif', $gifBytes);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/user/profile-picture', ['avatar' => $image]);

        $this->admin->refresh();
        $this->assertNotNull($this->admin->avatar);

        $deleteResponse = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson('/api/v1/user/profile-picture');

        $deleteResponse->assertStatus(200);

        $this->admin->refresh();
        $this->assertNull($this->admin->avatar);
        $this->assertNull($this->admin->avatar_url);
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_profile_with_preferences(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'preferences' => [
                'emailNotifications' => true,
                'autoApprove' => true,
                'maintenanceMode' => false,
            ],
        ]);

        $res = $this->actingAs($user)->getJson('/api/v1/user/profile');
        $res->assertStatus(200);
        $res->assertJsonPath('data.user.preferences.autoApprove', true);
        $res->assertJsonPath('data.user.email', $user->email);
    }

    public function test_user_can_update_profile_and_preferences(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $res = $this->actingAs($user)->putJson('/api/v1/user/profile', [
            'name' => 'Updated Admin Name',
            'email' => $user->email,
            'preferences' => [
                'emailNotifications' => false,
                'autoApprove' => true,
                'maintenanceMode' => true,
            ],
        ]);

        $res->assertStatus(200);
        $this->assertEquals('Updated Admin Name', $user->fresh()->name);
        $this->assertTrue($user->fresh()->preferences['autoApprove']);
        $this->assertFalse($user->fresh()->preferences['emailNotifications']);
        $this->assertTrue($user->fresh()->preferences['maintenanceMode']);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password123'),
        ]);

        $res = $this->actingAs($user)->putJson('/api/v1/user/password', [
            'current_password' => 'old-password123',
            'new_password' => 'new-secure-password456',
            'new_password_confirmation' => 'new-secure-password456',
        ]);

        $res->assertStatus(200);
        $this->assertTrue(Hash::check('new-secure-password456', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password123'),
        ]);

        $res = $this->actingAs($user)->putJson('/api/v1/user/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-secure-password456',
            'new_password_confirmation' => 'new-secure-password456',
        ]);

        $res->assertStatus(422);
        $this->assertFalse(Hash::check('new-secure-password456', $user->fresh()->password));
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Department;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_stats_endpoint_returns_real_counts(): void
    {
        $university = University::factory()->create(['is_active' => true]);
        Department::factory()->create(['university_id' => $university->id, 'is_active' => true]);
        Course::factory()->count(3)->create(['is_active' => true]);
        User::factory()->count(2)->create(['role' => 'student', 'account_status' => 'approved']);

        $response = $this->getJson('/api/v1/public/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'materials',
                    'students',
                    'courses',
                    'campuses',
                    'departments',
                    'notes',
                    'quizzes',
                    'campuses_list',
                ],
            ]);

        $this->assertEquals(3, $response->json('data.courses'));
        $this->assertEquals(2, $response->json('data.students'));
        $this->assertEquals(1, $response->json('data.campuses'));
        $this->assertEquals(1, $response->json('data.departments'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\StudyTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyTaskOverdueNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create([
            'role' => 'student',
            'account_status' => 'approved',
        ]);
    }

    public function test_overdue_task_generates_notification_on_tasks_index(): void
    {
        // Create an overdue uncompleted task (due 2 days ago)
        $task = StudyTask::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Read Chapter 4',
            'completed' => false,
            'due_date' => now()->subDays(2),
        ]);

        $this->actingAs($this->student)
            ->getJson('/api/v1/tasks')
            ->assertOk();

        // Notification should have been generated
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'task_overdue',
            'title' => 'Study Task Overdue',
        ]);

        $notification = AppNotification::where('user_id', $this->student->id)->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Read Chapter 4', $notification->message);
        $this->assertEquals('/tasks', $notification->action_url);
        $this->assertEquals($task->id, $notification->data['task_id']);
    }

    public function test_completing_overdue_task_marks_notification_as_read(): void
    {
        $task = StudyTask::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Review Lecture Notes',
            'completed' => false,
            'due_date' => now()->subDay(),
        ]);

        // Trigger notification creation
        $this->actingAs($this->student)
            ->getJson('/api/v1/notifications')
            ->assertOk();

        $notification = AppNotification::where('user_id', $this->student->id)->first();
        $this->assertNull($notification->read_at);

        // Complete the task
        $this->actingAs($this->student)
            ->postJson("/api/v1/tasks/{$task->id}/complete")
            ->assertOk();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_artisan_command_generates_overdue_notifications(): void
    {
        StudyTask::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Prepare for Exam',
            'completed' => false,
            'due_date' => now()->subDays(3),
        ]);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Sent 1 overdue notification(s).')
            ->assertExitCode(0);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'task_overdue',
        ]);
    }
}

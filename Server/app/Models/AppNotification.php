<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'action_url',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Check for overdue study tasks and create notifications if not already sent.
     */
    public static function checkAndNotifyOverdueTasks(?int $userId = null): int
    {
        $today = now()->startOfDay();

        $query = StudyTask::where('completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $overdueTasks = $query->get();
        $createdCount = 0;

        foreach ($overdueTasks as $task) {
            $formattedDate = \Carbon\Carbon::parse($task->due_date)->format('M d, Y');

            $alreadyNotified = static::where('user_id', $task->user_id)
                ->where('type', 'task_overdue')
                ->where('data->task_id', $task->id)
                ->exists();

            if (!$alreadyNotified) {
                static::create([
                    'user_id' => $task->user_id,
                    'type' => 'task_overdue',
                    'title' => 'Study Task Overdue',
                    'message' => "Your task \"{$task->title}\" passed its due date ({$formattedDate}) before being marked completed.",
                    'action_url' => '/tasks',
                    'data' => [
                        'task_id' => $task->id,
                        'due_date' => $task->due_date ? (is_string($task->due_date) ? $task->due_date : $task->due_date->toISOString()) : null,
                    ],
                ]);
                $createdCount++;
            }
        }

        return $createdCount;
    }
}

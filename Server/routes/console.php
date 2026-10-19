<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:check-overdue', function () {
    $count = \App\Models\AppNotification::checkAndNotifyOverdueTasks();
    $this->info("Checked overdue tasks. Sent {$count} overdue notification(s).");
})->purpose('Check for overdue study tasks and create student notifications');

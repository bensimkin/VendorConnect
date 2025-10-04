<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckTaskDeadlines extends Command
{
    protected $signature = 'tasks:check-deadlines';
    protected $description = 'Check task deadlines and create notifications';

    public function handle()
    {
        $this->info('Checking task deadlines...');

        $notificationService = new NotificationService();
        $processedCount = 0;

        // Check for tasks due soon (24 hours before due date)
        $tasksDueSoon = Task::where('end_date', '>', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addHours(24))
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'task_due_soon')
                      ->where('created_at', '>=', Carbon::now()->subHours(1));
            })
            ->with(['users', 'status'])
            ->get();

        foreach ($tasksDueSoon as $task) {
            // Skip completed and archived tasks
            if ($task->status && (strtolower($task->status->slug) === 'completed' || strtolower($task->status->slug) === 'archive')) {
                continue;
            }

            try {
                $notificationService->taskDueSoon($task);
                $processedCount++;
                $this->info("Created due soon notification for task: {$task->title}");
            } catch (\Exception $e) {
                $this->error("Failed to create due soon notification for task {$task->id}: {$e->getMessage()}");
            }
        }

        // Check for overdue tasks
        $overdueTasks = Task::where('end_date', '<', Carbon::now())
            ->whereDoesntHave('notifications', function ($query) {
                $query->where('type', 'task_overdue')
                      ->where('created_at', '>=', Carbon::now()->subHours(24));
            })
            ->with(['users', 'status'])
            ->get();

        foreach ($overdueTasks as $task) {
            // Skip completed and archived tasks
            if ($task->status && (strtolower($task->status->slug) === 'completed' || strtolower($task->status->slug) === 'archive')) {
                continue;
            }

            try {
                $notificationService->taskOverdue($task);
                $processedCount++;
                $this->info("Created overdue notification for task: {$task->title}");
            } catch (\Exception $e) {
                $this->error("Failed to create overdue notification for task {$task->id}: {$e->getMessage()}");
            }
        }

        $this->info("Processed {$processedCount} task deadline notifications.");
    }
}

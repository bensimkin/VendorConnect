<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Status;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoArchiveCompletedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:auto-archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically archive completed tasks after the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if auto-archive is enabled
            $autoArchiveEnabled = Setting::getValue('auto_archive_enabled', '0') === '1';
            
            if (!$autoArchiveEnabled) {
                $this->info('Auto-archive is disabled. Skipping...');
                return 0;
            }

            // Get the number of days to wait before archiving
            $archiveDays = (int) Setting::getValue('auto_archive_days', '30');
            
            if ($archiveDays <= 0) {
                $this->error('Invalid auto-archive days setting. Must be greater than 0.');
                return 1;
            }

            // Get the Completed and Archive statuses
            $completedStatus = Status::getCompletedStatus();
            $archiveStatus = Status::getArchiveStatus();

            if (!$completedStatus) {
                $this->error('Completed status not found. Cannot proceed with auto-archiving.');
                return 1;
            }

            if (!$archiveStatus) {
                $this->error('Archive status not found. Cannot proceed with auto-archiving.');
                return 1;
            }

            // Calculate the cutoff date
            $cutoffDate = Carbon::now()->subDays($archiveDays);

            // Find completed tasks that are older than the cutoff date
            $tasksToArchive = Task::where('status_id', $completedStatus->id)
                ->where('updated_at', '<=', $cutoffDate)
                ->get();

            if ($tasksToArchive->isEmpty()) {
                $this->info('No completed tasks found to archive.');
                return 0;
            }

            $this->info("Found {$tasksToArchive->count()} completed tasks to archive.");

            $archivedCount = 0;
            $errors = [];

            foreach ($tasksToArchive as $task) {
                try {
                    // Update task status to Archive
                    $task->update(['status_id' => $archiveStatus->id]);
                    $archivedCount++;
                    
                    Log::info("Auto-archived task: {$task->id} - {$task->title}");
                } catch (\Exception $e) {
                    $errors[] = "Failed to archive task {$task->id}: " . $e->getMessage();
                    Log::error("Failed to auto-archive task {$task->id}: " . $e->getMessage());
                }
            }

            // Report results
            $this->info("Successfully archived {$archivedCount} tasks.");

            if (!empty($errors)) {
                $this->error("Errors encountered:");
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
                return 1;
            }

            $this->info('Auto-archive completed successfully.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Auto-archive command failed: ' . $e->getMessage());
            Log::error('Auto-archive command failed: ' . $e->getMessage());
            return 1;
        }
    }
}
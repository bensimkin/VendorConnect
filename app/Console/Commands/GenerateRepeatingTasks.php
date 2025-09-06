<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateRepeatingTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:generate-repeating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate repeating tasks based on their schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to generate repeating tasks...');

        // Get all active repeating tasks
        $repeatingTasks = Task::where('is_repeating', true)
            ->where('repeat_active', true)
            ->whereNull('parent_task_id') // Only parent tasks
            ->get();

        $generatedCount = 0;

        foreach ($repeatingTasks as $parentTask) {
            $this->info("Processing task: {$parentTask->title}");

            // Check if we should stop repeating
            if ($parentTask->repeat_until && Carbon::parse($parentTask->repeat_until)->isPast()) {
                $this->info("Task {$parentTask->title} has reached its end date, stopping repetition.");
                $parentTask->update(['repeat_active' => false]);
                continue;
            }

            // Calculate next occurrence date
            $nextDate = $this->calculateNextOccurrence($parentTask);
            
            if (!$nextDate) {
                continue;
            }

            // Check if we already have a task for this date
            $existingTask = Task::where('parent_task_id', $parentTask->id)
                ->whereDate('start_date', $nextDate)
                ->first();

            if ($existingTask) {
                $this->info("Task already exists for {$nextDate}, skipping.");
                continue;
            }

            // Create the new task
            $newTask = $this->createRepeatingTask($parentTask, $nextDate);
            
            if ($newTask) {
                $generatedCount++;
                $this->info("Created new task: {$newTask->title} for {$nextDate}");
            }
        }

        $this->info("Generated {$generatedCount} new repeating tasks.");
    }

    /**
     * Calculate the next occurrence date for a repeating task
     */
    private function calculateNextOccurrence(Task $task): ?Carbon
    {
        // Check if we should start repeating yet
        if ($task->repeat_start) {
            $repeatStartDate = Carbon::parse($task->repeat_start);
            // If repeat_start is in the future, don't generate tasks yet
            if ($repeatStartDate->isFuture()) {
                return null;
            }
        }

        // Determine the base date for calculation
        $baseDate = $task->last_repeated_at ? Carbon::parse($task->last_repeated_at) : Carbon::parse($task->start_date);
        
        $interval = $task->repeat_interval;
        $now = Carbon::now();

        switch ($task->repeat_frequency) {
            case 'daily':
                $nextDate = $baseDate->copy()->addDays($interval);
                break;
            case 'weekly':
                $nextDate = $baseDate->copy()->addWeeks($interval);
                break;
            case 'monthly':
                $nextDate = $baseDate->copy()->addMonths($interval);
                break;
            case 'yearly':
                $nextDate = $baseDate->copy()->addYears($interval);
                break;
            default:
                return null;
        }

        // Only return if the next date is in the future
        return $nextDate->isFuture() ? $nextDate : null;
    }

    /**
     * Create a new repeating task
     */
    private function createRepeatingTask(Task $parentTask, Carbon $nextDate): ?Task
    {
        try {
            DB::beginTransaction();

            // Calculate end date based on original task duration
            $originalStart = Carbon::parse($parentTask->start_date);
            $originalEnd = Carbon::parse($parentTask->end_date);
            $duration = $originalStart->diffInDays($originalEnd);
            $newEndDate = $nextDate->copy()->addDays($duration);

            // Format the date for the title (e.g., "5 Aug 2025")
            $formattedDate = $nextDate->format('j M Y');
            $newTitle = $parentTask->title . ' ' . $formattedDate;
            
            $newTask = Task::create([
                'title' => $newTitle,
                'description' => $parentTask->description,
                'status_id' => $parentTask->status_id,
                'priority_id' => $parentTask->priority_id,
                'task_type_id' => $parentTask->task_type_id,
                'project_id' => $parentTask->project_id,
                'start_date' => $nextDate->format('Y-m-d'),
                'end_date' => $newEndDate->format('Y-m-d'),
                'note' => $parentTask->note,
                'deliverable_quantity' => $parentTask->deliverable_quantity,
                'close_deadline' => $parentTask->close_deadline,
                'created_by' => $parentTask->created_by,
                'is_repeating' => false, // Child tasks are not repeating
                'parent_task_id' => $parentTask->id,
            ]);

            // Note: User and tag assignments are not copied to avoid relationship issues
            // These can be manually assigned if needed

            // Update parent task's last_repeated_at
            $parentTask->update(['last_repeated_at' => now()]);

            DB::commit();
            return $newTask;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to create repeating task: " . $e->getMessage());
            return null;
        }
    }
}

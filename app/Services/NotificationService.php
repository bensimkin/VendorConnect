<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskDeliverable;
use App\Models\ChMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a notification for task assignment
     */
    public function taskAssigned(Task $task, User $assignedUser): void
    {
        $this->createNotification(
            user: $assignedUser,
            type: Notification::TYPE_TASK_ASSIGNED,
            title: 'New Task Assigned',
            message: "You have been assigned a new task: {$task->title}",
            data: [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'project_id' => $task->project_id,
                'project_name' => $task->project?->title,
                'assigned_by' => $task->created_by,
            ],
            actionUrl: "/tasks/{$task->id}",
            priority: $this->getPriorityFromTask($task)
        );
    }

    /**
     * Create a notification for task completion
     */
    public function taskCompleted(Task $task, User $completedBy): void
    {
        // Notify task creator
        if ($task->created_by !== $completedBy->id) {
            $creator = User::find($task->created_by);
            if ($creator) {
                $this->createNotification(
                    user: $creator,
                    type: Notification::TYPE_TASK_COMPLETED,
                    title: 'Task Completed',
                    message: "Task '{$task->title}' has been completed by {$completedBy->first_name} {$completedBy->last_name}",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'completed_by' => $completedBy->id,
                        'completed_by_name' => "{$completedBy->first_name} {$completedBy->last_name}",
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_MEDIUM
                );
            }
        }

        // Notify other assigned users
        foreach ($task->users as $user) {
            if ($user->id !== $completedBy->id && $user->id !== $task->created_by) {
                $this->createNotification(
                    user: $user,
                    type: Notification::TYPE_TASK_COMPLETED,
                    title: 'Task Completed',
                    message: "Task '{$task->title}' has been completed by {$completedBy->first_name} {$completedBy->last_name}",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'completed_by' => $completedBy->id,
                        'completed_by_name' => "{$completedBy->first_name} {$completedBy->last_name}",
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_MEDIUM
                );
            }
        }
    }

    /**
     * Create a notification for task due soon
     */
    public function taskDueSoon(Task $task): void
    {
        $hoursUntilDue = Carbon::now()->diffInHours($task->end_date, false);
        
        foreach ($task->users as $user) {
            $this->createNotification(
                user: $user,
                type: Notification::TYPE_TASK_DUE_SOON,
                title: 'Task Due Soon',
                message: "Task '{$task->title}' is due in {$hoursUntilDue} hours",
                data: [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_date' => $task->end_date,
                    'hours_until_due' => $hoursUntilDue,
                ],
                actionUrl: "/tasks/{$task->id}",
                priority: Notification::PRIORITY_HIGH
            );
        }

        // Also notify task creator if different from assigned users
        if ($task->created_by && !$task->users->contains('id', $task->created_by)) {
            $creator = User::find($task->created_by);
            if ($creator) {
                $this->createNotification(
                    user: $creator,
                    type: Notification::TYPE_TASK_DUE_SOON,
                    title: 'Task Due Soon',
                    message: "Task '{$task->title}' is due in {$hoursUntilDue} hours",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'due_date' => $task->end_date,
                        'hours_until_due' => $hoursUntilDue,
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_HIGH
                );
            }
        }
    }

    /**
     * Create a notification for overdue tasks
     */
    public function taskOverdue(Task $task): void
    {
        $daysOverdue = Carbon::now()->diffInDays($task->end_date);
        
        foreach ($task->users as $user) {
            $this->createNotification(
                user: $user,
                type: Notification::TYPE_TASK_OVERDUE,
                title: 'Task Overdue',
                message: "Task '{$task->title}' is {$daysOverdue} days overdue",
                data: [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_date' => $task->end_date,
                    'days_overdue' => $daysOverdue,
                ],
                actionUrl: "/tasks/{$task->id}",
                priority: Notification::PRIORITY_URGENT
            );
        }

        // Also notify task creator if different from assigned users
        if ($task->created_by && !$task->users->contains('id', $task->created_by)) {
            $creator = User::find($task->created_by);
            if ($creator) {
                $this->createNotification(
                    user: $creator,
                    type: Notification::TYPE_TASK_OVERDUE,
                    title: 'Task Overdue',
                    message: "Task '{$task->title}' is {$daysOverdue} days overdue",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'due_date' => $task->end_date,
                        'days_overdue' => $daysOverdue,
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_URGENT
                );
            }
        }
    }

    /**
     * Create a notification for new deliverables
     */
    public function deliverableAdded(TaskDeliverable $deliverable, User $addedBy): void
    {
        $task = $deliverable->task;
        
        // Notify task creator
        if ($task->created_by !== $addedBy->id) {
            $creator = User::find($task->created_by);
            if ($creator) {
                $this->createNotification(
                    user: $creator,
                    type: Notification::TYPE_DELIVERABLE_ADDED,
                    title: 'New Deliverable Added',
                    message: "A new deliverable has been added to task '{$task->title}' by {$addedBy->first_name} {$addedBy->last_name}",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'deliverable_id' => $deliverable->id,
                        'deliverable_title' => $deliverable->title,
                        'added_by' => $addedBy->id,
                        'added_by_name' => "{$addedBy->first_name} {$addedBy->last_name}",
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_MEDIUM
                );
            }
        }

        // Notify other assigned users
        foreach ($task->users as $user) {
            if ($user->id !== $addedBy->id && $user->id !== $task->created_by) {
                $this->createNotification(
                    user: $user,
                    type: Notification::TYPE_DELIVERABLE_ADDED,
                    title: 'New Deliverable Added',
                    message: "A new deliverable has been added to task '{$task->title}' by {$addedBy->first_name} {$addedBy->last_name}",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'deliverable_id' => $deliverable->id,
                        'deliverable_title' => $deliverable->title,
                        'added_by' => $addedBy->id,
                        'added_by_name' => "{$addedBy->first_name} {$addedBy->last_name}",
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_MEDIUM
                );
            }
        }
    }

    /**
     * Create a notification for new comments
     */
    public function commentAdded(ChMessage $message, User $addedBy): void
    {
        $task = $message->task;
        
        // Notify task creator
        if ($task->created_by !== $addedBy->id) {
            $creator = User::find($task->created_by);
            if ($creator) {
                $this->createNotification(
                    user: $creator,
                    type: Notification::TYPE_COMMENT_ADDED,
                    title: 'New Comment on Task',
                    message: "{$addedBy->first_name} {$addedBy->last_name} commented on task '{$task->title}'",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'message_id' => $message->id,
                        'comment' => substr($message->body, 0, 100) . (strlen($message->body) > 100 ? '...' : ''),
                        'added_by' => $addedBy->id,
                        'added_by_name' => "{$addedBy->first_name} {$addedBy->last_name}",
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_LOW
                );
            }
        }

        // Notify other assigned users
        foreach ($task->users as $user) {
            if ($user->id !== $addedBy->id && $user->id !== $task->created_by) {
                $this->createNotification(
                    user: $user,
                    type: Notification::TYPE_COMMENT_ADDED,
                    title: 'New Comment on Task',
                    message: "{$addedBy->first_name} {$addedBy->last_name} commented on task '{$task->title}'",
                    data: [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'message_id' => $message->id,
                        'comment' => substr($message->body, 0, 100) . (strlen($message->body) > 100 ? '...' : ''),
                        'added_by' => $addedBy->id,
                        'added_by_name' => "{$addedBy->first_name} {$addedBy->last_name}",
                    ],
                    actionUrl: "/tasks/{$task->id}",
                    priority: Notification::PRIORITY_LOW
                );
            }
        }
    }

    /**
     * Create a notification for project updates
     */
    public function projectUpdated($project, User $updatedBy, string $updateType): void
    {
        // Notify project creator
        if ($project->created_by !== $updatedBy->id) {
            $creator = User::find($project->created_by);
            if ($creator) {
                $this->createNotification(
                    user: $creator,
                    type: Notification::TYPE_PROJECT_UPDATED,
                    title: 'Project Updated',
                    message: "Project '{$project->title}' has been {$updateType} by {$updatedBy->first_name} {$updatedBy->last_name}",
                    data: [
                        'project_id' => $project->id,
                        'project_title' => $project->title,
                        'update_type' => $updateType,
                        'updated_by' => $updatedBy->id,
                        'updated_by_name' => "{$updatedBy->first_name} {$updatedBy->last_name}",
                    ],
                    actionUrl: "/projects/{$project->id}",
                    priority: Notification::PRIORITY_MEDIUM
                );
            }
        }

        // Notify project users
        foreach ($project->users as $user) {
            if ($user->id !== $updatedBy->id && $user->id !== $project->created_by) {
                $this->createNotification(
                    user: $user,
                    type: Notification::TYPE_PROJECT_UPDATED,
                    title: 'Project Updated',
                    message: "Project '{$project->title}' has been {$updateType} by {$updatedBy->first_name} {$updatedBy->last_name}",
                    data: [
                        'project_id' => $project->id,
                        'project_title' => $project->title,
                        'update_type' => $updateType,
                        'updated_by' => $updatedBy->id,
                        'updated_by_name' => "{$updatedBy->first_name} {$updatedBy->last_name}",
                    ],
                    actionUrl: "/projects/{$project->id}",
                    priority: Notification::PRIORITY_MEDIUM
                );
            }
        }
    }

    /**
     * Create a notification for client updates
     */
    public function clientUpdated($client, User $updatedBy, string $updateType): void
    {
        // Notify admins and sub-admins
        $admins = User::role(['admin', 'sub_admin'])->get();
        
        foreach ($admins as $admin) {
            if ($admin->id !== $updatedBy->id) {
                $this->createNotification(
                    user: $admin,
                    type: Notification::TYPE_CLIENT_UPDATED,
                    title: 'Client Updated',
                    message: "Client '{$client->name}' has been {$updateType} by {$updatedBy->first_name} {$updatedBy->last_name}",
                    data: [
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                        'update_type' => $updateType,
                        'updated_by' => $updatedBy->id,
                        'updated_by_name' => "{$updatedBy->first_name} {$updatedBy->last_name}",
                    ],
                    actionUrl: "/clients/{$client->id}",
                    priority: Notification::PRIORITY_LOW
                );
            }
        }
    }

    /**
     * Create a scheduled notification for task due soon
     */
    public function scheduleTaskDueSoonNotification(Task $task, int $hoursBeforeDue = 24): void
    {
        if (!$task->end_date) return;

        $scheduledTime = Carbon::parse($task->end_date)->subHours($hoursBeforeDue);
        
        // Only schedule if it's in the future
        if ($scheduledTime->isFuture()) {
            foreach ($task->users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_TASK_DUE_SOON,
                    'title' => 'Task Due Soon',
                    'message' => "Task '{$task->title}' is due in {$hoursBeforeDue} hours",
                    'data' => [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'due_date' => $task->end_date,
                        'hours_before_due' => $hoursBeforeDue,
                    ],
                    'action_url' => "/tasks/{$task->id}",
                    'priority' => Notification::PRIORITY_HIGH,
                    'scheduled_at' => $scheduledTime,
                ]);
            }

            // Also schedule for task creator if different
            if ($task->created_by && !$task->users->contains('id', $task->created_by)) {
                Notification::create([
                    'user_id' => $task->created_by,
                    'type' => Notification::TYPE_TASK_DUE_SOON,
                    'title' => 'Task Due Soon',
                    'message' => "Task '{$task->title}' is due in {$hoursBeforeDue} hours",
                    'data' => [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'due_date' => $task->end_date,
                        'hours_before_due' => $hoursBeforeDue,
                    ],
                    'action_url' => "/tasks/{$task->id}",
                    'priority' => Notification::PRIORITY_HIGH,
                    'scheduled_at' => $scheduledTime,
                ]);
            }
        }
    }

    /**
     * Create a scheduled notification for task overdue
     */
    public function scheduleTaskOverdueNotification(Task $task, int $daysAfterDue = 1): void
    {
        if (!$task->end_date) return;

        $scheduledTime = Carbon::parse($task->end_date)->addDays($daysAfterDue);
        
        // Only schedule if it's in the future
        if ($scheduledTime->isFuture()) {
            foreach ($task->users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_TASK_OVERDUE,
                    'title' => 'Task Overdue',
                    'message' => "Task '{$task->title}' is {$daysAfterDue} day(s) overdue",
                    'data' => [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'due_date' => $task->end_date,
                        'days_after_due' => $daysAfterDue,
                    ],
                    'action_url' => "/tasks/{$task->id}",
                    'priority' => Notification::PRIORITY_URGENT,
                    'scheduled_at' => $scheduledTime,
                ]);
            }

            // Also schedule for task creator if different
            if ($task->created_by && !$task->users->contains('id', $task->created_by)) {
                Notification::create([
                    'user_id' => $task->created_by,
                    'type' => Notification::TYPE_TASK_OVERDUE,
                    'title' => 'Task Overdue',
                    'message' => "Task '{$task->title}' is {$daysAfterDue} day(s) overdue",
                    'data' => [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'due_date' => $task->end_date,
                        'days_after_due' => $daysAfterDue,
                    ],
                    'action_url' => "/tasks/{$task->id}",
                    'priority' => Notification::PRIORITY_URGENT,
                    'scheduled_at' => $scheduledTime,
                ]);
            }
        }
    }

    /**
     * Get priority based on task priority and deadline
     */
    private function getPriorityFromTask(Task $task): string
    {
        if ($task->priority) {
            switch (strtolower($task->priority->name)) {
                case 'urgent':
                    return Notification::PRIORITY_URGENT;
                case 'high':
                    return Notification::PRIORITY_HIGH;
                case 'medium':
                    return Notification::PRIORITY_MEDIUM;
                case 'low':
                case 'not urgent':
                    return Notification::PRIORITY_LOW;
            }
        }

        return Notification::PRIORITY_MEDIUM;
    }

    /**
     * Create a notification
     */
    private function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        string $priority = Notification::PRIORITY_MEDIUM
    ): void {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl,
                'priority' => $priority
            ]);

            Log::info("Notification created", [
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create notification", [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

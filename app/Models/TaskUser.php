<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    use HasFactory;

    public $table = 'task_user';

    public $timestamps = false;

    protected $fillable = [
        'task_id', 'user_id', 'last_activity_at'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * Update the last activity timestamp for a user on a task
     * Only updates if task is not completed or archived
     */
    public static function updateActivity(int $taskId, int $userId): void
    {
        try {
            // Check if task is completed or archived
            $taskStatus = \DB::table('tasks')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->where('tasks.id', $taskId)
                ->value('statuses.slug');

            // Don't track activity for completed or archived tasks
            if ($taskStatus && in_array($taskStatus, ['completed', 'archive'])) {
                return;
            }

            \DB::table('task_user')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update(['last_activity_at' => now()]);
        } catch (\Exception $e) {
            // Fail silently - don't break user actions
            \Log::debug('Task activity update failed', [
                'task_id' => $taskId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}

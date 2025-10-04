<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentHistory extends Model
{
    use HasFactory;

    protected $table = 'task_assignment_history';

    protected $fillable = [
        'task_id',
        'user_id',
        'client_id',
        'action',
        'action_date',
        'action_by',
    ];

    protected $casts = [
        'action_date' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Check if a user has ever been assigned to a task for a specific client
     */
    public static function hasUserEverBeenAssignedToClient($userId, $clientId)
    {
        return self::where('user_id', $userId)
            ->where('client_id', $clientId)
            ->where('action', 'assigned')
            ->exists();
    }

    /**
     * Get all clients a user has ever been assigned to tasks for
     */
    public static function getClientsUserHasEverBeenAssignedTo($userId)
    {
        return self::where('user_id', $userId)
            ->where('action', 'assigned')
            ->with('client')
            ->get()
            ->pluck('client')
            ->unique('id');
    }
}

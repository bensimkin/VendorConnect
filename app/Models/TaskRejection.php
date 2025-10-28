<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRejection extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'rejected_at',
        'reason',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the task that was rejected
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who rejected the task
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

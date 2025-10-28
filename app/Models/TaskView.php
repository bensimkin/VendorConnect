<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskView extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'viewed_at',
        'view_duration_seconds',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the task that was viewed
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who viewed the task
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;
    protected $fillable = [
        "task_type",
        "created_at",
        "updated_at"
    ];

    // protected $appends = ['name'];  // REMOVED: Causes duplicate fields in API

    public function briefTemplate()
    {
        return $this->hasMany(TaskBriefTemplates::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // public function getNameAttribute()
    // {
    //     return $this->task_type;
    // }
    // REMOVED: Causes duplicate fields in API responses
}

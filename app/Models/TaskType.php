<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;
    protected $fillable = [
        'admin_id',
        "task_type",
        "created_at",
        "updated_at"
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }


    public function briefTemplate()
    {
        return $this->hasMany(TaskBriefTemplates::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'file_path',
        'file_category',
        'file_name',
        'file_type',
        'file_size',
        'description',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}


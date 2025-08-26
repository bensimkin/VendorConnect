<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBriefTemplates extends Model
{
    use HasFactory;
    protected $fillable =[
        "title",
        "standard_brief",
        "description",
        "deliverable_quantity",
        "task_type_id",
        "created_at",
        "updated_at"
    ];


    public function taskType(){
        return $this->belongsTo(TaskType::class);
    }

    public function briefQuestions()
    {
        return $this->hasMany(TaskBriefQuestion::class);
    }

    public function briefchecks()
    {
        return $this->hasMany(TaskBriefChecklist::class);
    }

}

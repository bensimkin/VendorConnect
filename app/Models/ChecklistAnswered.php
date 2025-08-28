<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChecklistAnswered extends Model
{

    protected $table = 'checklist_answereds';
    protected $fillable = [
        'task_id',
        'checklist_id',
        'answer_by',
        'checklist_answer',
    ];

    protected $casts = [
        'checklist_answer' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function briefChecklist()
    {
        return $this->belongsTo(TaskBriefChecklist::class, 'checklist_id');
    }

}

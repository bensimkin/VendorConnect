<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswered extends Model
{

    protected $fillable = [
        'task_id',
        'question_id',
        'answer_by',
        'check_brief',
        'question_answer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // public function briefQuestions()
    // {
    //     return $this->hasOne(TaskBriefQuestion::class);
    // }

    public function briefQuestions()
    {
        return $this->belongsTo(TaskBriefQuestion::class, 'question_id');
    }

}

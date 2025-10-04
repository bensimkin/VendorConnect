<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBriefQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        "task_brief_templates_id",
        "question_text",
        "question_type",
        "options",
        "created_at",
        "updated_at",
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function briefTemplate()
    {
        return $this->belongsTo(TaskBriefTemplates::class);
    }
    public function questionAnswered()
    {
        return $this->hasOne(QuestionAnswered::class, 'question_id');
    }

    public static function boot()
    {
        parent::boot();

        // Handle deleting event
        static::deleting(function ($taskBriefQuestion) {
            $taskBriefQuestion->questionAnswered()->delete();
        });
    }
}

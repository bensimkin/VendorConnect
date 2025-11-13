<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBriefChecklist extends Model
{
    use HasFactory;
    protected $table = 'task_brief_checklists';
    protected $fillable = [
        'admin_id',
        "task_brief_templates_id",
        "checklist",
        "created_at",
        "updated_at",
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    protected $casts = [
        "checklist" => 'array'
    ];

    public function briefTemplate()
    {
        return $this->belongsTo(TaskBriefTemplates::class);
    }
}

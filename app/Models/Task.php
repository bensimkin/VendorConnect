<?php

namespace App\Models;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'title',
        'status_id',
        'task_type_id',
        'template_id',
        'project_id',
        'start_date',
        'close_deadline',
        'end_date',
        'description',
        'standard_brief',
        'user_id',
        'admin_id',
        'created_by',
        'priority_id',
        'note',
        'deliverable_quantity',
        'is_repeating',
        'repeat_frequency',
        'repeat_interval',
        'skip_weekends',
        'repeat_until',
        'repeat_start',
        'repeat_active',
        'parent_task_id',
        'last_repeated_at',
        'template_questions',
        'template_checklist',
        'template_standard_brief',
        'template_description',
        'template_deliverable_quantity',
    ];

    protected $casts = [
        'is_repeating' => 'boolean',
        'repeat_active' => 'boolean',
        'skip_weekends' => 'boolean',
        'repeat_until' => 'date',
        'repeat_start' => 'date',
        'last_repeated_at' => 'datetime',
        'template_questions' => 'array',
        'template_checklist' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        // $media_storage_settings = get_settings('media_storage_settings');
        // $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';
        // if ($mediaStorageType === 's3') {
        //     $this->addMediaCollection('task-media')->useDisk('s3');
        // } else {
        //     $this->addMediaCollection('task-media')->useDisk('public');
        // }
        $this->addMediaCollection('task-media')->useDisk('public');
    }

    public function questionAnswers()
    {
        return $this->hasMany(QuestionAnswered::class);
    }

    public function checklistAnswers()
    {
        return $this->hasMany(ChecklistAnswered::class);
    }

    public function messages()
    {
        return $this->hasMany(ChMessage::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
    public function clients()
    {
        return $this->project ? $this->project->clients : collect();
    }

    public function taskUsers()
    {
        return $this->belongsToMany(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    public function template()
    {
        return $this->belongsTo(TaskBriefTemplates::class, 'template_id');
    }

    public function getresult()
    {
        return substr($this->title, 0, 100);
    }

    public function getlink()
    {
        return str( route('tasks.info',['id' => $this->id]));
    }

    // public function workspace()
    // {
    //     return $this->belongsTo(Workspace::class);
    // }
    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function portfolio()
    {
        return $this->hasOne(Portfolio::class);
    }

    public function deliverables()
    {
        return $this->hasMany(TaskDeliverable::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function childTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

}

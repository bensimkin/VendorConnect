<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Portfolio extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'task_id',
        'project_id',
        'title',
        'description',
        'deliverable_type', // 'design', 'document', 'presentation', 'other'
        'status', // 'completed', 'in_progress', 'review'
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portfolio-media')->useDisk('public');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function taskType()
    {
        return $this->hasOneThrough(TaskType::class, Task::class, 'id', 'id', 'task_id', 'task_type_id');
    }

    public function getDeliverableTypeLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->deliverable_type));
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFileCountAttribute()
    {
        return $this->getMedia('portfolio-media')->count();
    }

    public function getMainFileAttribute()
    {
        return $this->getMedia('portfolio-media')->first();
    }
}

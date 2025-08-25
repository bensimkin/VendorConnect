<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TaskDeliverable extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'type',
        'file_path',
        'google_link',
        'external_link',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('deliverable-files')->useDisk('public');
    }

    public function getLinkAttribute()
    {
        if ($this->google_link) {
            return $this->google_link;
        }
        if ($this->external_link) {
            return $this->external_link;
        }
        return null;
    }

    public function getLinkTypeAttribute()
    {
        if ($this->google_link) {
            return 'google';
        }
        if ($this->external_link) {
            return 'external';
        }
        return null;
    }
}

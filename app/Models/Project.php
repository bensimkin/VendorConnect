<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'title',
        'status_id',
        'budget',
        'start_date',
        'end_date',
        'description',
        'user_id',
        'client_id',
        'workspace_id',
        'admin_id',
        'created_by',
        'priority_id',
        'note',
        'task_accessibility',
    ];

    public function registerMediaCollections(): void
    {
        $media_storage_settings = get_settings('media_storage_settings');
        $mediaStorageType = $media_storage_settings['media_storage_type'] ?? 'local';
        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('project-media')->useDisk('s3');
        } else {
            $this->addMediaCollection('project-media')->useDisk('public');
        }
    }

    public function scopeFilter($query, array $filters)
    {
        if ($filters['search_projects'] ?? false) {
            $query->where('title', 'like', '%' . request('search_projects') . '%')
                ->orWhere('status', 'like', '%' . request('search_projects') . '%');
        }
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function taskUsers()
    {
        return $this->hasManyThrough(User::class, Task::class, 'project_id', 'id', 'id', 'user_id')
            ->join('task_user', 'users.id', '=', 'task_user.user_id')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->where('tasks.project_id', $this->id)
            ->distinct();
    }

    /**
     * Get all team members including direct project users and task users
     */
    public function getAllTeamMembers()
    {
        // Get direct project users
        $directUsers = $this->users;
        
        // Get users assigned to tasks within this project
        $taskUsers = User::whereHas('tasks', function($query) {
            $query->where('project_id', $this->id);
        })->get();
        
        // Merge and return unique users
        return $directUsers->merge($taskUsers)->unique('id');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function getresult()
    {

        return substr($this->title, 0, 100);
    }

    public function getlink()
    {
        return str(route('projects.info', ['id' => $this->id]));
    }
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }
    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }
}

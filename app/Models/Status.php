<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Status extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'slug',
        'admin_id'
    ];


    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // public function tasks()
    // {
    //     return $this->hasMany(Task::class)->where('tasks.workspace_id', session()->get('workspace_id'));
    // }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function user_tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_status');
    }

    /**
     * Check if this status is the Archive status
     */
    public function isArchive()
    {
        return $this->slug === 'archive';
    }

    /**
     * Check if this status is the Completed status
     */
    public function isCompleted()
    {
        return $this->slug === 'completed';
    }

    /**
     * Get the Archive status
     */
    public static function getArchiveStatus()
    {
        return static::where('slug', 'archive')->first();
    }

    /**
     * Get the Completed status
     */
    public static function getCompletedStatus()
    {
        return static::where('slug', 'completed')->first();
    }

    /**
     * Get statuses that can be set by Taskers
     */
    public static function getTaskerAllowedStatuses()
    {
        return static::whereIn('slug', ['pending', 'submitted'])->get();
    }

    /**
     * Get statuses that can be set by Requesters and Admins
     */
    public static function getRequesterAdminAllowedStatuses()
    {
        return static::whereNotIn('slug', ['pending', 'submitted'])->get();
    }

}

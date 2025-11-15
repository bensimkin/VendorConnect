<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'profile',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'photo',
        'dob',
        'doj',
        'status',
        'client_id',
        'email_verified_at',
        'last_login_at',
        'country_code',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'notification_preferences' => 'array',
    ];

    protected $appends = ['role'];

    public function scopeFilter($query, array $filters)
    {
        if ($filters['search'] ?? false) {
            $query->where('first_name', 'like', '%' . request('search') . '%')
                ->orWhere('last_name', 'like', '%' . request('search') . '%')
                ->orWhere('role', 'like', '%' . request('search') . '%');
        }
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function userTask()
    {
        return $this->belongsToMany(Task::class);
    }

    /**
     * Get all sessions for this user
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function status_tasks($status_id)
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->where('tasks.status_id', $status_id);
    }

    public function status_projects($status_id)
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->where('projects.status_id', $status_id);
    }

    public function project_tasks($project_id)
    {
        return $this->belongsToMany(Task::class, 'task_user')
        ->where('tasks.project_id', $project_id)->get();
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profile()
    {
        return $this->morphOne(Profile::class, 'profileable');
    }

    public function getresult()
    {
        return str($this->first_name . " " . $this->last_name);
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function notes($search = '', $orderBy = 'id', $direction = 'desc')
    {
        $query = Note::where(function ($query) {
            $query->where('creator_id', 'u_' . $this->getKey())
                ->where('workspace_id', session()->get('workspace_id'));
        });

        if ($search !== '') {
            $query->where('title', 'like', '%' . $search . '%');
        }
        $query->orderBy($orderBy, $direction);
        return $query->get();
    }

    public function can($ability, $arguments = [])
    {
        $isAdmin = $this->hasRole('admin'); // Check if the user has the 'admin' role

        // Check if the user is an admin or has the specific permission
        if ($isAdmin || $this->hasPermissionTo($ability)) {
            return true;
        }

        // For other cases, use the original can() method
        return parent::can($ability, $arguments);
    }


    public function getlink()
    {
        return str(route('users.show', [$this->id]));
    }

    /**
     * Get the primary role name for display
     */
    public function getRoleAttribute()
    {
        $roles = $this->roles;
        if ($roles->count() > 0) {
            // Return the first role name, or join multiple roles with commas
            return $roles->pluck('name')->implode(', ');
        }
        return 'User';
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id', 'id');
    }

    public function estimates_invoices()
    {
        return EstimatesInvoice::where(function ($query) {
            $query->where('created_by', 'u_' . $this->getKey());
        })->get();
    }

    public function expenses()
    {
        $userId = $this->getKey(); // Get the current user's ID

        return $this->hasMany(Expense::class, 'user_id')
            ->orWhere(function ($query) use ($userId) {
                $query->where('created_by', 'u_' . $userId);
            });
    }

    public function payments()
    {
        $userId = $this->getKey(); // Get the current user's ID

        return $this->hasMany(Payment::class, 'user_id')
            ->orWhere(function ($query) use ($userId) {
            $query->where('created_by', 'u_' . $userId);
        });
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_user');
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Send the password reset notification using SendGrid.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\SendGridPasswordReset($token));
    }

    /**
     * Send the email verification notification using SendGrid.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\SendGridEmailVerification);
    }

    public function taskViews()
    {
        return $this->hasMany(TaskView::class);
    }

    public function taskRejections()
    {
        return $this->hasMany(TaskRejection::class);
    }
}

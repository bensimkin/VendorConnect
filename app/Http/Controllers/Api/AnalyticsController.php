<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController;
use App\Models\TaskView;
use App\Models\TaskRejection;
use App\Models\UserSession;
use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use App\Models\ProjectMetricsBaseline;
use App\Models\ChMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends BaseController
{
    /**
     * Track a task view
     */
    public function trackTaskView(Request $request, $taskId)
    {
        try {
            $user = Auth::user();
            
            // Validate that the task exists
            $task = Task::find($taskId);
            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Check if this is the first view by this user for this task
            $firstView = TaskView::where('task_id', $taskId)
                ->where('user_id', $user->id)
                ->orderBy('viewed_at', 'asc')
                ->first();

            // Get or create the view record
            $taskView = TaskView::create([
                'task_id' => $taskId,
                'user_id' => $user->id,
                'viewed_at' => now(),
                'view_duration_seconds' => $request->input('view_duration_seconds'),
            ]);

            return $this->sendResponse([
                'view_id' => $taskView->id,
                'task_id' => $taskId,
                'user_id' => $user->id,
                'viewed_at' => $taskView->viewed_at,
                'view_duration_seconds' => $taskView->view_duration_seconds,
                'is_first_view' => $firstView === null, // This view is the first view
            ], 'Task view tracked successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error tracking task view: ' . $e->getMessage());
        }
    }

    /**
     * Get task view analytics (admin only)
     */
    public function getTaskViewAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view task analytics');
            }

            // Get query parameters
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $taskId = $request->input('task_id');
            $userId = $request->input('user_id');

            // Build query
            $query = DB::table('task_views')
                ->join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('users', 'task_views.user_id', '=', 'users.id')
                ->leftJoin('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
                ->select(
                    'task_views.id',
                    'task_views.task_id',
                    'task_views.user_id',
                    'task_views.viewed_at',
                    'task_views.view_duration_seconds',
                    'tasks.title as task_title',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'statuses.title as status_title',
                    'projects.name as project_name'
                );

            // Apply filters
            if ($startDate) {
                $query->where('task_views.viewed_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('task_views.viewed_at', '<=', $endDate);
            }
            if ($taskId) {
                $query->where('task_views.task_id', $taskId);
            }
            if ($userId) {
                $query->where('task_views.user_id', $userId);
            }

            // Get paginated results
            $results = $query->orderBy('task_views.viewed_at', 'desc')
                ->paginate($request->get('per_page', 50));

            return $this->sendPaginatedResponse($results, 'Task view analytics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task view analytics: ' . $e->getMessage());
        }
    }

    /**
     * Get task view statistics dashboard (admin only)
     */
    public function getTaskViewStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view task statistics');
            }

            // Get query parameters (default to 7 days)
            $startDate = $request->input('start_date', Carbon::now()->subDays(7)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            // Total views (exclude completed and archived tasks)
            $totalViews = TaskView::join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->whereBetween('task_views.viewed_at', [$startDate, $endDate])
                ->whereNotIn('statuses.slug', ['completed', 'archive'])
                ->count();

            // Unique tasks viewed (exclude completed and archived tasks)
            $uniqueTasksViewed = TaskView::join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->whereBetween('task_views.viewed_at', [$startDate, $endDate])
                ->whereNotIn('statuses.slug', ['completed', 'archive'])
                ->distinct('task_views.task_id')
                ->count('task_views.task_id');

            // Unique users viewing tasks (exclude completed and archived tasks)
            $uniqueUsers = TaskView::join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->whereBetween('task_views.viewed_at', [$startDate, $endDate])
                ->whereNotIn('statuses.slug', ['completed', 'archive'])
                ->distinct('task_views.user_id')
                ->count('task_views.user_id');

            // Most viewed tasks (exclude completed and archived tasks)
            $mostViewedTasks = TaskView::join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->whereBetween('task_views.viewed_at', [$startDate, $endDate])
                ->whereNotIn('statuses.slug', ['completed', 'archive'])
                ->select('task_views.task_id', DB::raw('count(*) as view_count'))
                ->groupBy('task_views.task_id')
                ->orderBy('view_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $task = Task::find($item->task_id);
                    return [
                        'task_id' => $item->task_id,
                        'task_title' => $task ? $task->title : 'Unknown',
                        'view_count' => $item->view_count,
                    ];
                });

            // Top users by views (exclude completed and archived tasks)
            $topUsers = TaskView::join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->whereBetween('task_views.viewed_at', [$startDate, $endDate])
                ->whereNotIn('statuses.slug', ['completed', 'archive'])
                ->select('task_views.user_id', DB::raw('count(*) as view_count'))
                ->groupBy('task_views.user_id')
                ->orderBy('view_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $user = User::find($item->user_id);
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $user ? $user->name : 'Unknown',
                        'user_email' => $user ? $user->email : 'Unknown',
                        'view_count' => $item->view_count,
                    ];
                });

            // Tasks with no views (assigned 3+ days ago but never opened, exclude completed and archived)
            $unviewedTasks = Task::whereHas('users')
                ->whereHas('status', function($q) {
                    $q->whereNotIn('slug', ['completed', 'archive']);
                })
                ->where('created_at', '<=', Carbon::now()->subDays(3)->toDateString())
                ->whereDoesntHave('views')
                ->with(['users', 'status', 'priority'])
                ->get()
                ->map(function ($task) {
                    return [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'assigned_users' => $task->users->map(fn($u) => $u->name)->toArray(),
                        'status_title' => $task->status ? $task->status->title : null,
                        'priority_title' => $task->priority ? $task->priority->title : null,
                        'days_since_assignment' => Carbon::parse($task->created_at)->diffInDays(now()),
                    ];
                });

            // Average view duration (exclude completed and archived tasks)
            $avgViewDuration = TaskView::join('tasks', 'task_views.task_id', '=', 'tasks.id')
                ->join('statuses', 'tasks.status_id', '=', 'statuses.id')
                ->whereBetween('task_views.viewed_at', [$startDate, $endDate])
                ->whereNotIn('statuses.slug', ['completed', 'archive'])
                ->whereNotNull('task_views.view_duration_seconds')
                ->avg('task_views.view_duration_seconds');

            return $this->sendResponse([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'overview' => [
                    'total_views' => $totalViews,
                    'unique_tasks_viewed' => $uniqueTasksViewed,
                    'unique_users' => $uniqueUsers,
                    'average_view_duration_seconds' => $avgViewDuration ? round($avgViewDuration, 2) : null,
                ],
                'most_viewed_tasks' => $mostViewedTasks,
                'top_users' => $topUsers,
                'unviewed_tasks' => $unviewedTasks,
            ], 'Task view statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task view statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get task rejection statistics (admin only)
     */
    public function getTaskRejectionStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view task rejection statistics');
            }

            // Get query parameters (default to 30 days)
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            // Total rejections
            $totalRejections = TaskRejection::whereBetween('rejected_at', [$startDate, $endDate])->count();

            // Unique tasks rejected
            $uniqueTasksRejected = TaskRejection::whereBetween('rejected_at', [$startDate, $endDate])
                ->distinct('task_id')
                ->count('task_id');

            // Unique users rejecting tasks
            $uniqueUsers = TaskRejection::whereBetween('rejected_at', [$startDate, $endDate])
                ->distinct('user_id')
                ->count('user_id');

            // Top users by rejections
            $topRejectingUsers = TaskRejection::whereBetween('rejected_at', [$startDate, $endDate])
                ->select('user_id', DB::raw('count(*) as rejection_count'))
                ->groupBy('user_id')
                ->orderBy('rejection_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $user = User::find($item->user_id);
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $user ? $user->name : 'Unknown',
                        'user_email' => $user ? $user->email : 'Unknown',
                        'rejection_count' => $item->rejection_count,
                    ];
                });

            // Most rejected tasks
            $mostRejectedTasks = TaskRejection::whereBetween('rejected_at', [$startDate, $endDate])
                ->select('task_id', DB::raw('count(*) as rejection_count'))
                ->groupBy('task_id')
                ->orderBy('rejection_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $task = Task::with('taskType')->find($item->task_id);
                    return [
                        'task_id' => $item->task_id,
                        'task_title' => $task ? $task->title : 'Unknown',
                        'task_type' => $task && $task->taskType ? $task->taskType->title : null,
                        'rejection_count' => $item->rejection_count,
                    ];
                });

            // Recent rejections with details
            $recentRejections = TaskRejection::whereBetween('rejected_at', [$startDate, $endDate])
                ->with(['task', 'user'])
                ->orderBy('rejected_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($rejection) {
                    return [
                        'rejection_id' => $rejection->id,
                        'task_id' => $rejection->task_id,
                        'task_title' => $rejection->task ? $rejection->task->title : 'Unknown',
                        'user_id' => $rejection->user_id,
                        'user_name' => $rejection->user ? $rejection->user->name : 'Unknown',
                        'user_email' => $rejection->user ? $rejection->user->email : 'Unknown',
                        'rejected_at' => $rejection->rejected_at,
                        'reason' => $rejection->reason,
                    ];
                });

            return $this->sendResponse([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'overview' => [
                    'total_rejections' => $totalRejections,
                    'unique_tasks_rejected' => $uniqueTasksRejected,
                    'unique_users_rejecting' => $uniqueUsers,
                ],
                'top_rejecting_users' => $topRejectingUsers,
                'most_rejected_tasks' => $mostRejectedTasks,
                'recent_rejections' => $recentRejections,
            ], 'Task rejection statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task rejection statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get user session statistics (admin only)
     */
    public function getUserSessionStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view user session statistics');
            }

            // Get query parameters (default to 7 days)
            $startDate = $request->input('start_date', Carbon::now()->subDays(7)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            // Total sessions
            $totalSessions = UserSession::whereBetween('login_at', [$startDate, $endDate])->count();

            // Active users (users with sessions in the last 24 hours)
            $activeUsers = UserSession::whereNull('logout_at')
                ->where('last_activity_at', '>=', now()->subHours(24))
                ->distinct('user_id')
                ->count('user_id');

            // Unique users
            $uniqueUsers = UserSession::whereBetween('login_at', [$startDate, $endDate])
                ->distinct('user_id')
                ->count('user_id');

            // Average session duration
            $avgSessionDuration = UserSession::whereBetween('login_at', [$startDate, $endDate])
                ->whereNotNull('duration_seconds')
                ->avg('duration_seconds');

            // Top users by session count
            $topUsersBySessions = UserSession::whereBetween('login_at', [$startDate, $endDate])
                ->select('user_id', DB::raw('count(*) as session_count'))
                ->groupBy('user_id')
                ->orderBy('session_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $user = User::find($item->user_id);
                    $avgDuration = UserSession::where('user_id', $item->user_id)
                        ->whereNotNull('duration_seconds')
                        ->avg('duration_seconds');
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $user ? $user->name : 'Unknown',
                        'user_email' => $user ? $user->email : 'Unknown',
                        'session_count' => $item->session_count,
                        'avg_duration_seconds' => $avgDuration ? round($avgDuration, 2) : null,
                    ];
                });

            // Recent sessions
            $recentSessions = UserSession::whereBetween('login_at', [$startDate, $endDate])
                ->with('user')
                ->orderBy('login_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($session) {
                    return [
                        'session_id' => $session->id,
                        'user_id' => $session->user_id,
                        'user_name' => $session->user ? $session->user->name : 'Unknown',
                        'login_at' => $session->login_at,
                        'logout_at' => $session->logout_at,
                        'duration_seconds' => $session->duration_seconds,
                    ];
                });

            // Inactive users with active tasks (haven't logged in for 5+ days)
            $inactiveUsers = User::whereHas('tasks', function($q) {
                    $status = Status::where('title', 'Completed')->first();
                    if ($status) {
                        $q->where('tasks.status_id', '!=', $status->id);
                    }
                })
                ->where('last_login_at', '<', now()->subDays(5))
                ->limit(20)
                ->get()
                ->map(function ($user) {
                    $activeTasksCount = $user->tasks()->where(function($q) {
                        $status = Status::where('title', 'Completed')->first();
                        if ($status) {
                            $q->where('status_id', '!=', $status->id);
                        }
                    })->count();
                    
                    return [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'last_login_at' => $user->last_login_at,
                        'active_tasks_count' => $activeTasksCount,
                        'days_since_login' => $user->last_login_at ? Carbon::parse($user->last_login_at)->diffInDays(now()) : null,
                    ];
                });

            // Session activity trend (daily breakdown)
            $sessionActivityTrend = UserSession::whereBetween('login_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(login_at) as date'),
                    DB::raw('COUNT(DISTINCT user_id) as active_users'),
                    DB::raw('COUNT(*) as total_sessions')
                )
                ->groupBy(DB::raw('DATE(login_at)'))
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'active_users' => $item->active_users,
                        'total_sessions' => $item->total_sessions,
                    ];
                });

            return $this->sendResponse([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'overview' => [
                    'total_sessions' => $totalSessions,
                    'active_users' => $activeUsers,
                    'unique_users' => $uniqueUsers,
                    'average_session_duration_seconds' => $avgSessionDuration ? round($avgSessionDuration, 2) : null,
                ],
                'top_users_by_sessions' => $topUsersBySessions,
                'recent_sessions' => $recentSessions,
                'inactive_users' => $inactiveUsers,
                'session_activity_trend' => $sessionActivityTrend,
            ], 'User session statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving user session statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get user task activity statistics (admin only)
     */
    public function getUserTaskActivityStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view task activity analytics');
            }

            // Get query parameters
            $startDate = $request->input('start_date', Carbon::now()->subDays(7)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            // Overview statistics (exclude completed/archived tasks)
            $totalTaskAssignments = DB::table('task_user as tu')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->whereNotNull('tu.last_activity_at')
                ->whereBetween('tu.last_activity_at', [$startDate, $endDate])
                ->whereNotIn('t.status_id', function($q) {
                    $q->select('id')->from('statuses')->whereIn('slug', ['completed', 'archive']);
                })
                ->count();

            $uniqueTasksWithActivity = DB::table('task_user as tu')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->whereNotNull('tu.last_activity_at')
                ->whereBetween('tu.last_activity_at', [$startDate, $endDate])
                ->whereNotIn('t.status_id', function($q) {
                    $q->select('id')->from('statuses')->whereIn('slug', ['completed', 'archive']);
                })
                ->distinct('tu.task_id')
                ->count('tu.task_id');

            $uniqueUsersWithActivity = DB::table('task_user as tu')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->whereNotNull('tu.last_activity_at')
                ->whereBetween('tu.last_activity_at', [$startDate, $endDate])
                ->whereNotIn('t.status_id', function($q) {
                    $q->select('id')->from('statuses')->whereIn('slug', ['completed', 'archive']);
                })
                ->distinct('tu.user_id')
                ->count('tu.user_id');

            // Tasks with no activity from assigned users (7+ days)
            $tasksWithNoActivity = DB::table('task_user as tu')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->leftJoin('statuses as s', 't.status_id', '=', 's.id')
                ->leftJoin('priorities as p', 't.priority_id', '=', 'p.id')
                ->join('users as u', 'tu.user_id', '=', 'u.id')
                ->where(function($q) {
                    $q->whereNull('tu.last_activity_at')
                    ->orWhere('tu.last_activity_at', '<', Carbon::now()->subDays(7));
                })
                ->whereNotIn('t.status_id', function($q) {
                    $q->select('id')->from('statuses')->whereIn('slug', ['completed', 'archive']);
                })
                ->select(
                    'tu.task_id',
                    'tu.last_activity_at',
                    't.created_at',
                    't.title as task_title',
                    's.title as status_title',
                    'p.title as priority_title',
                    DB::raw('GROUP_CONCAT(CONCAT(u.first_name, " ", u.last_name) SEPARATOR ", ") as assigned_users')
                )
                ->groupBy('tu.task_id', 't.title', 's.title', 'p.title', 'tu.last_activity_at', 't.created_at')
                ->get()
                ->map(function ($task) {
                    return [
                        'task_id' => $task->task_id,
                        'task_title' => $task->task_title,
                        'status_title' => $task->status_title,
                        'priority_title' => $task->priority_title,
                        'assigned_users' => explode(', ', $task->assigned_users),
                        'days_since_activity' => Carbon::parse($task->last_activity_at ?? $task->created_at)->diffInDays(now()),
                    ];
                });

            $usersByActivityLevel = DB::table('task_user as tu')
                ->join('users as u', 'tu.user_id', '=', 'u.id')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->whereNotNull('tu.last_activity_at')
                ->whereBetween('tu.last_activity_at', [$startDate, $endDate])
                ->whereNotIn('t.status_id', function($q) {
                    $q->select('id')->from('statuses')->whereIn('slug', ['completed', 'archive']);
                })
                ->select(
                    'tu.user_id',
                    DB::raw('CONCAT(u.first_name, " ", u.last_name) as user_name'),
                    'u.email as user_email',
                    DB::raw('COUNT(DISTINCT tu.task_id) as active_tasks_count'),
                    DB::raw('MAX(tu.last_activity_at) as most_recent_activity')
                )
                ->groupBy('tu.user_id', 'u.first_name', 'u.last_name', 'u.email')
                ->orderByDesc(DB::raw('COUNT(DISTINCT tu.task_id)'))
                ->limit(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'user_id' => $user->user_id,
                        'user_name' => $user->user_name,
                        'user_email' => $user->user_email,
                        'active_tasks_count' => (int) $user->active_tasks_count,
                        'most_recent_activity' => Carbon::parse($user->most_recent_activity)->toDateTimeString(),
                    ];
                });

            // Tasks with most activity
            $mostActiveTasks = DB::table('task_user as tu')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->whereNotNull('tu.last_activity_at')
                ->whereBetween('tu.last_activity_at', [$startDate, $endDate])
                ->whereNotIn('t.status_id', function ($q) {
                    $q->select('id')
                        ->from('statuses')
                        ->whereIn('slug', ['completed', 'archive']);
                })
                ->select(
                    'tu.task_id',
                    't.title as task_title',
                    DB::raw('COUNT(DISTINCT tu.user_id) as active_users_count'),
                    DB::raw('MAX(tu.last_activity_at) as last_activity')
                )
                ->groupBy('tu.task_id', 't.title')
                ->orderBy('active_users_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($task) {
                    return [
                        'task_id' => $task->task_id,
                        'task_title' => $task->task_title,
                        'active_users_count' => $task->active_users_count,
                        'last_activity' => $task->last_activity,
                    ];
                });

            // Activity trend
            $activityTrend = DB::table('task_user as tu')
                ->join('tasks as t', 'tu.task_id', '=', 't.id')
                ->whereNotNull('tu.last_activity_at')
                ->whereBetween('tu.last_activity_at', [$startDate, $endDate])
                ->whereNotIn('t.status_id', function ($q) {
                    $q->select('id')
                        ->from('statuses')
                        ->whereIn('slug', ['completed', 'archive']);
                })
                ->select(
                    DB::raw('DATE(tu.last_activity_at) as date'),
                    DB::raw('COUNT(DISTINCT tu.task_id) as active_tasks'),
                    DB::raw('COUNT(DISTINCT tu.user_id) as active_users')
                )
                ->groupBy(DB::raw('DATE(tu.last_activity_at)'))
                ->orderBy('date', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'active_tasks' => $item->active_tasks,
                        'active_users' => $item->active_users,
                    ];
                });

            return $this->sendResponse([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'overview' => [
                    'total_activities' => $totalTaskAssignments,
                    'unique_tasks_with_activity' => $uniqueTasksWithActivity,
                    'unique_users_with_activity' => $uniqueUsersWithActivity,
                ],
                'tasks_with_no_activity' => $tasksWithNoActivity,
                'users_by_activity_level' => $usersByActivityLevel,
                'most_active_tasks' => $mostActiveTasks,
                'activity_trend' => $activityTrend,
            ], 'User task activity statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving user task activity statistics: ' . $e->getMessage());
        }
    }

    /**
     * Get project metrics baselines (admin only)
     */
    public function getProjectMetricsBaselines(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view project metrics baselines');
            }

            // Get all baseline metrics
            $baselines = ProjectMetricsBaseline::orderBy('calculated_at', 'desc')
                ->with(['taskType', 'client'])
                ->get();

            // Group by metric name for easier display
            $groupedBaselines = [
                'overall_metrics' => [],
                'by_client' => [],
                'by_task_type' => [],
            ];

            foreach ($baselines as $baseline) {
                if ($baseline->client_id) {
                    $groupedBaselines['by_client'][] = [
                        'metric_name' => $baseline->metric_name,
                        'metric_value' => $baseline->metric_value,
                        'sample_size' => $baseline->sample_size,
                        'client' => $baseline->client ? [
                            'id' => $baseline->client->id,
                            'name' => $baseline->client->company ?? ($baseline->client->first_name . ' ' . $baseline->client->last_name),
                        ] : null,
                        'calculated_at' => $baseline->calculated_at,
                    ];
                } elseif ($baseline->task_type_id) {
                    $groupedBaselines['by_task_type'][] = [
                        'metric_name' => $baseline->metric_name,
                        'metric_value' => $baseline->metric_value,
                        'sample_size' => $baseline->sample_size,
                        'task_type' => $baseline->taskType ? [
                            'id' => $baseline->taskType->id,
                            'name' => $baseline->taskType->task_type,
                        ] : null,
                        'calculated_at' => $baseline->calculated_at,
                    ];
                } else {
                    $groupedBaselines['overall_metrics'][] = [
                        'metric_name' => $baseline->metric_name,
                        'metric_value' => $baseline->metric_value,
                        'sample_size' => $baseline->sample_size,
                        'calculated_at' => $baseline->calculated_at,
                    ];
                }
            }

            return $this->sendResponse([
                'baselines' => $groupedBaselines,
                'summary' => [
                    'total_baselines' => $baselines->count(),
                    'last_calculated' => $baselines->first() ? $baselines->first()->calculated_at : null,
                ],
            ], 'Project metrics baselines retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving project metrics baselines: ' . $e->getMessage());
        }
    }

    /**
     * Get comment activity statistics (admin only)
     */
    public function getCommentActivityStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$this->hasAdminAccess($user)) {
                return $this->sendForbidden('Only admins can view comment activity statistics');
            }

            // Get query parameters (default to 7 days)
            $startDate = $request->input('start_date', Carbon::now()->subDays(7)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            // Total comments
            $totalComments = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Unique tasks with comments
            $uniqueTasksWithComments = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct('task_id')
                ->count('task_id');

            // Unique users commenting
            $uniqueUsers = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct('sender_id')
                ->count('sender_id');

            // Average comments per task
            $avgCommentsPerTask = $totalComments > 0 ? round($totalComments / $uniqueTasksWithComments, 2) : 0;

            // Tasks with most comments
            $mostActiveTasks = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('task_id', DB::raw('count(*) as comment_count'))
                ->groupBy('task_id')
                ->orderBy('comment_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $task = Task::find($item->task_id);
                    return [
                        'task_id' => $item->task_id,
                        'task_title' => $task ? $task->title : 'Unknown',
                        'comment_count' => $item->comment_count,
                    ];
                });

            // Top users by comments
            $topCommentingUsers = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('sender_id', DB::raw('count(*) as comment_count'))
                ->groupBy('sender_id')
                ->orderBy('comment_count', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $user = User::find($item->sender_id);
                    return [
                        'user_id' => $item->sender_id,
                        'user_name' => $user ? ($user->first_name . ' ' . $user->last_name) : 'Unknown',
                        'user_email' => $user ? $user->email : 'Unknown',
                        'comment_count' => $item->comment_count,
                    ];
                });

            // Tasks with no comments in last 7 days (but active)
            $inactiveTasks = Task::whereHas('users')
                ->whereHas('status', function($q) {
                    $q->whereNotIn('slug', ['completed', 'archive']);
                })
                ->whereDoesntHave('messages', function($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                })
                ->with(['users', 'status', 'priority'])
                ->limit(20)
                ->get()
                ->map(function ($task) {
                    return [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'assigned_users' => $task->users->map(fn($u) => $u->first_name . ' ' . $u->last_name)->toArray(),
                        'status_title' => $task->status ? $task->status->title : null,
                        'priority_title' => $task->priority ? $task->priority->title : null,
                    ];
                });

            // Comment activity trend
            $activityTrend = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_comments'),
                    DB::raw('COUNT(DISTINCT task_id) as active_tasks'),
                    DB::raw('COUNT(DISTINCT sender_id) as active_users')
                )
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'total_comments' => $item->total_comments,
                        'active_tasks' => $item->active_tasks,
                        'active_users' => $item->active_users,
                    ];
                });

            // Recent comments
            $recentComments = ChMessage::whereNotNull('task_id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with(['sender', 'task'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($message) {
                    return [
                        'comment_id' => $message->id,
                        'task_id' => $message->task_id,
                        'task_title' => $message->task ? $message->task->title : 'Unknown',
                        'sender_id' => $message->sender_id,
                        'sender_name' => $message->sender ? ($message->sender->first_name . ' ' . $message->sender->last_name) : 'Unknown',
                        'sender_email' => $message->sender ? $message->sender->email : 'Unknown',
                        'comment_text' => mb_strimwidth($message->message_text ?? '', 0, 100, '...'),
                        'created_at' => $message->created_at,
                    ];
                });

            return $this->sendResponse([
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'overview' => [
                    'total_comments' => $totalComments,
                    'unique_tasks_with_comments' => $uniqueTasksWithComments,
                    'unique_users_commenting' => $uniqueUsers,
                    'average_comments_per_task' => $avgCommentsPerTask,
                ],
                'most_active_tasks' => $mostActiveTasks,
                'top_commenting_users' => $topCommentingUsers,
                'tasks_with_no_recent_comments' => $inactiveTasks,
                'recent_comments' => $recentComments,
                'activity_trend' => $activityTrend,
            ], 'Comment activity statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving comment activity statistics: ' . $e->getMessage());
        }
    }
}

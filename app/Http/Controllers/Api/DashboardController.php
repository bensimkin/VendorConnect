<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\TaskDeliverable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends BaseController
{
    /**
     * Get dashboard data
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $timeRange = $request->get('time_range', 'all'); // 'all', '30_days', '7_days'
            
            // Get date range based on time_range parameter
            $dateRange = $this->getDateRange($timeRange);

            // Get basic counts with role-based filtering
            $totalTasksQuery = Task::query();
            
            // Apply date filtering if not 'all'
            if ($dateRange['start']) {
                $totalTasksQuery->where('created_at', '>=', $dateRange['start']);
            }

            if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
                // Admins and sub-admins see all tasks
            } elseif ($user->hasRole('Requester')) {
                $totalTasksQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('Tasker')) {
                $totalTasksQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }

            $totalTasks = $totalTasksQuery->count();
            
            // Apply date filtering to other counts
            $totalUsers = User::count(); // Users count doesn't change with date range
            $totalClients = Client::count(); // Clients count doesn't change with date range
            
            $totalProjectsQuery = Project::query();
            if ($dateRange['start']) {
                $totalProjectsQuery->where('created_at', '>=', $dateRange['start']);
            }
            $totalProjects = $totalProjectsQuery->count();

            // Get task statistics
            $taskStats = $this->getTaskStatistics($dateRange);
            
            // Get recent tasks with role-based filtering
            $recentTasksQuery = Task::with(['users', 'status', 'priority', 'taskType', 'template', 'deliverables']);
            
            // Apply date filtering if not 'all'
            if ($dateRange['start']) {
                $recentTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            // Apply role-based filtering for recent tasks
            if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
                // Admins and sub-admins see all tasks (no additional filtering)
            } elseif ($user->hasRole('Requester')) {
                // Requesters only see tasks they created
                $recentTasksQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('Tasker')) {
                // Taskers only see tasks they're assigned to
                $recentTasksQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
            
            $recentTasks = $recentTasksQuery
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Note: deliverables_count is now automatically available via Task model accessor

            // Get user activity
            $userActivity = $this->getUserActivity($dateRange);

            // Get task completion trend
            $taskTrend = $this->getTaskCompletionTrend($dateRange);

            // Get project management data
            $projectManagement = $this->getProjectManagementData($user, $dateRange);

            // Get overdue tasks
            $overdueTasks = $this->getOverdueTasks($user, $dateRange);

            // Get all statuses for frontend mapping
            $statuses = Status::select('id', 'title')->get();

            // Get additional statistics for admin dashboard
            $rejectedTasksTrend = $this->getRejectedTasksTrend($dateRange);
            $tasksWithUncheckedChecklists = $this->getTasksWithUncheckedChecklists($dateRange);
            $averageTaskCompletionTime = $this->getAverageTaskCompletionTime($dateRange);
            $additionalStats = $this->getAdditionalStatistics($dateRange);

            $dashboardData = [
                'overview' => [
                    'total_tasks' => $totalTasks,
                    'total_users' => $totalUsers,
                    'total_clients' => $totalClients,
                    'total_projects' => $totalProjects,
                ],
                'task_statistics' => $taskStats,
                'recent_tasks' => $recentTasks,
                'overdue_tasks' => $overdueTasks,
                'user_activity' => $userActivity,
                'task_trend' => $taskTrend,
                'project_management' => $projectManagement,
                'statuses' => $statuses,
                'rejected_tasks_trend' => $rejectedTasksTrend,
                'tasks_with_unchecked_checklists' => $tasksWithUncheckedChecklists,
                'average_task_completion_time' => $averageTaskCompletionTime,
                'additional_statistics' => $additionalStats,
            ];

            return $this->sendResponse($dashboardData, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get date range based on time range parameter
     */
    private function getDateRange($timeRange)
    {
        switch ($timeRange) {
            case '7_days':
                return [
                    'start' => Carbon::now()->subDays(7)->startOfDay(),
                    'end' => Carbon::now()->endOfDay(),
                    'label' => 'Last 7 days'
                ];
            case '30_days':
                return [
                    'start' => Carbon::now()->subDays(30)->startOfDay(),
                    'end' => Carbon::now()->endOfDay(),
                    'label' => 'Last 30 days'
                ];
            default:
                return [
                    'start' => null,
                    'end' => null,
                    'label' => 'All time'
                ];
        }
    }

    /**
     * Get task statistics
     */
    private function getTaskStatistics($dateRange = null)
    {
        $user = Auth::user();
        
        // Apply role-based filtering to task statistics
        $taskQuery = Task::query();
        
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $taskQuery->where('created_at', '>=', $dateRange['start']);
        }

        if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
            // Admins and sub-admins see all tasks (no additional filtering)
        } elseif ($user->hasRole('Requester')) {
            // Requesters only see tasks they created
            $taskQuery->where('created_by', $user->id);
        } elseif ($user->hasRole('Tasker')) {
            // Taskers only see tasks they're assigned to
            $taskQuery->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        
        $stats = $taskQuery->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get();

        $statusCounts = [];
        foreach ($stats as $stat) {
            $statusCounts[$stat->status_id] = $stat->count;
        }

        // Get status IDs dynamically
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        // Apply same filtering to completed and overdue counts
        $completedThisWeek = $completedStatusId ? (clone $taskQuery)->where('status_id', $completedStatusId)
            ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count() : 0;
            
        $overdue = (clone $taskQuery)->where('end_date', '<', Carbon::now())
            ->where('status_id', '!=', $completedStatusId) // Not completed
            ->count();
        
        return [
            'by_status' => $statusCounts,
            'completed_this_week' => $completedThisWeek,
            'overdue' => $overdue,
        ];
    }

    /**
     * Get user activity
     */
    private function getUserActivity($dateRange = null)
    {
        $user = Auth::user();
        
        $userQuery = User::withCount(['userTask as recent_tasks' => function ($query) use ($dateRange) {
                if ($dateRange && $dateRange['start']) {
                    $query->where('created_at', '>=', $dateRange['start']);
                } else {
                    $query->where('created_at', '>=', Carbon::now()->subDays(7));
                }
            }])
            ->having('recent_tasks', '>', 0) // Only show users with recent activity
            ->orderBy('recent_tasks', 'desc')
            ->limit(5);
        
        // Role-based data protection
        if ($user->hasRole(['admin', 'sub_admin'])) {
            // Admins and sub-admins can see all user data including emails
            return $userQuery->get(['id', 'first_name', 'last_name', 'email']);
        } else {
            // Requesters and taskers cannot see email addresses
            return $userQuery->get(['id', 'first_name', 'last_name']);
        }
    }

    /**
     * Get task completion trend
     */
    private function getTaskCompletionTrend($dateRange = null)
    {
        // Get status IDs dynamically
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        $trend = [];
        
        // Determine the number of days to show based on date range
        $daysToShow = 7; // Default
        if ($dateRange) {
            if ($dateRange['start']) {
                $daysToShow = min(30, Carbon::now()->diffInDays($dateRange['start']) + 1);
            }
        }
        
        for ($i = $daysToShow - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $query = Task::where('status_id', $completedStatusId)
                ->whereDate('updated_at', $date);
            
            // Apply date range filtering if provided
            if ($dateRange && $dateRange['start']) {
                $query->where('created_at', '>=', $dateRange['start']);
            }
            
            $count = $completedStatusId ? $query->count() : 0;
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'completed_tasks' => $count,
            ];
        }

        return $trend;
    }

    /**
     * Get project management data
     */
    private function getProjectManagementData($user, $dateRange = null)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;

        $projectQuery = Project::with(['status', 'clients'])
            ->withCount(['tasks as total_tasks' => function ($query) use ($dateRange) {
                if ($dateRange && $dateRange['start']) {
                    $query->where('created_at', '>=', $dateRange['start']);
                }
            }])
            ->withCount(['tasks as active_tasks' => function ($query) use ($completedStatusId, $dateRange) {
                if ($completedStatusId) {
                    $query->where('status_id', '!=', $completedStatusId);
                }
                if ($dateRange && $dateRange['start']) {
                    $query->where('created_at', '>=', $dateRange['start']);
                }
            }])
            ->withCount(['tasks as overdue_tasks' => function ($query) use ($completedStatusId, $dateRange) {
                $query->where('end_date', '<', Carbon::now());
                if ($completedStatusId) {
                    $query->where('status_id', '!=', $completedStatusId);
                }
                if ($dateRange && $dateRange['start']) {
                    $query->where('created_at', '>=', $dateRange['start']);
                }
            }])
            ->withCount(['tasks as completed_this_week_tasks' => function ($query) use ($completedStatusId, $dateRange) {
                if ($completedStatusId) {
                    $query->where('status_id', $completedStatusId)
                        ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                }
                if ($dateRange && $dateRange['start']) {
                    $query->where('created_at', '>=', $dateRange['start']);
                }
            }]);

        // Apply role-based filtering
        if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
            // Admins and sub-admins see all projects
        } elseif ($user->hasRole('Requester')) {
            // Requesters only see projects they created or are involved with
            $projectQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function ($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        } elseif ($user->hasRole('tasker')) {
            // Taskers only see projects they have tasks assigned to
            $projectQuery->whereHas('tasks', function ($q) use ($user) {
                $q->whereHas('users', function ($userQuery) use ($user) {
                    $userQuery->where('users.id', $user->id);
                });
            });
        }

        return $projectQuery->orderBy('updated_at', 'desc')->limit(10)->get();
    }

    /**
     * Get tasker dashboard data
     */
    public function taskerDashboard(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('Tasker')) {
                return $this->sendError('Unauthorized access', [], 403);
            }
            
            $timeRange = $request->get('time_range', 'all'); // 'all', '30_days', '7_days'
            $dateRange = $this->getDateRange($timeRange);

            // Get completed status ID
            $completedStatus = Status::where('title', 'Completed')->first();
            $completedStatusId = $completedStatus ? $completedStatus->id : null;

            // Overview statistics
            $totalTasksQuery = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
            
            if ($dateRange['start']) {
                $totalTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $totalTasks = $totalTasksQuery->count();

            $completedTasksQuery = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->where('status_id', $completedStatusId);
            
            if ($dateRange['start']) {
                $completedTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $completedTasks = $completedTasksQuery->count();

            $pendingTasksQuery = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->where('status_id', '!=', $completedStatusId);
            
            if ($dateRange['start']) {
                $pendingTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $pendingTasks = $pendingTasksQuery->count();

            $overdueTasksQuery = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->where('end_date', '<', Carbon::now())
              ->where('status_id', '!=', $completedStatusId);
              
            if ($dateRange['start']) {
                $overdueTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $overdueTasks = $overdueTasksQuery->count();

            // Recent tasks
            $recentTasksQuery = Task::with(['status', 'priority', 'project', 'template', 'deliverables'])
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
                
            if ($dateRange['start']) {
                $recentTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $recentTasks = $recentTasksQuery
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Note: deliverables_count is now automatically available via Task model accessor

            // Upcoming deadlines (next 7 days)
            $upcomingDeadlines = Task::with(['priority'])
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->where('end_date', '>=', Carbon::now())
                ->where('end_date', '<=', Carbon::now()->addDays(7))
                ->where('status_id', '!=', $completedStatusId)
                ->orderBy('end_date', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($task) {
                    $task->days_until_due = Carbon::now()->diffInDays($task->end_date, false);
                    return $task;
                });

            // Recent deliverables
            $recentDeliverables = TaskDeliverable::with(['task'])
                ->whereHas('task.users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($deliverable) {
                    return [
                        'id' => $deliverable->id,
                        'title' => $deliverable->title,
                        'task_title' => $deliverable->task->title,
                        'created_at' => $deliverable->created_at,
                    ];
                });

            // Get overdue tasks list
            $overdueTasksList = Task::with(['users', 'status', 'priority', 'project.clients'])
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->where('end_date', '<', Carbon::now())
                ->where('status_id', '!=', $completedStatusId)
                ->orderBy('end_date', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'end_date' => $task->end_date,
                        'status' => $task->status ? [
                            'id' => $task->status->id,
                            'title' => $task->status->title,
                        ] : null,
                        'priority' => $task->priority ? [
                            'id' => $task->priority->id,
                            'title' => $task->priority->title,
                        ] : null,
                        'project' => $task->project ? [
                            'id' => $task->project->id,
                            'title' => $task->project->title,
                        ] : null,
                        'clients' => $task->project && $task->project->clients ? $task->project->clients->map(function ($client) {
                            return [
                                'id' => $client->id,
                                'first_name' => $client->first_name,
                                'last_name' => $client->last_name,
                                'name' => $client->name,
                            ];
                        }) : [],
                        'users' => $task->users ? $task->users->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name,
                            ];
                        }) : [],
                        'created_at' => $task->created_at,
                    ];
                });

            // Get task statistics for charts (filtered for tasker's tasks)
            $taskStatistics = $this->getTaskerTaskStatistics($user, $dateRange);
            
            // Get task completion trend for charts (filtered for tasker's tasks)
            $taskTrend = $this->getTaskerTaskCompletionTrend($user, $dateRange);

            // Get user activity (filtered for tasker's tasks)
            $userActivity = $this->getTaskerUserActivity($user, $dateRange);

            $dashboardData = [
                'overview' => [
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'pending_tasks' => $pendingTasks,
                    'overdue_tasks' => $overdueTasks,
                ],
                'recent_tasks' => $recentTasks,
                'upcoming_deadlines' => $upcomingDeadlines,
                'recent_deliverables' => $recentDeliverables,
                'overdue_tasks' => $overdueTasksList,
                'task_statistics' => $taskStatistics,
                'task_trend' => $taskTrend,
                'user_activity' => $userActivity,
            ];

            return $this->sendResponse($dashboardData, 'Tasker dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving tasker dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get requester dashboard data
     */
    public function requesterDashboard(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('Requester')) {
                return $this->sendError('Unauthorized access', [], 403);
            }
            
            $timeRange = $request->get('time_range', 'all'); // 'all', '30_days', '7_days'
            $dateRange = $this->getDateRange($timeRange);

            // Get completed status ID
            $completedStatus = Status::where('title', 'Completed')->first();
            $completedStatusId = $completedStatus ? $completedStatus->id : null;

            // Overview statistics - include both created and assigned tasks/projects
            $totalTasksQuery = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            });
            
            if ($dateRange['start']) {
                $totalTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $totalTasks = $totalTasksQuery->count();
            
            $completedTasksQuery = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->where('status_id', $completedStatusId);
            
            if ($dateRange['start']) {
                $completedTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $completedTasks = $completedTasksQuery->count();
            
            $pendingTasksQuery = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->where('status_id', '!=', $completedStatusId);
            
            if ($dateRange['start']) {
                $pendingTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $pendingTasks = $pendingTasksQuery->count();
            
            $overdueTasksQuery = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->where('end_date', '<', Carbon::now())
              ->where('status_id', '!=', $completedStatusId);
              
            if ($dateRange['start']) {
                $overdueTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $overdueTasks = $overdueTasksQuery->count();
            
            $totalProjectsQuery = Project::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            });
            
            if ($dateRange['start']) {
                $totalProjectsQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $totalProjects = $totalProjectsQuery->count();

            // Recent tasks - include both created and assigned tasks
            $recentTasksQuery = Task::with(['status', 'priority', 'project', 'users', 'template', 'deliverables'])
                ->where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                });
                
            if ($dateRange['start']) {
                $recentTasksQuery->where('created_at', '>=', $dateRange['start']);
            }
            
            $recentTasks = $recentTasksQuery
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Note: deliverables_count is now automatically available via Task model accessor

            // Recent projects - include both created and assigned projects
            $recentProjects = Project::with(['status'])
                ->where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->withCount(['tasks as total_tasks'])
                ->withCount(['tasks as completed_tasks' => function ($query) use ($completedStatusId) {
                    if ($completedStatusId) {
                        $query->where('status_id', $completedStatusId);
                    }
                }])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Recent deliverables from their tasks (both created and assigned)
            $recentDeliverables = TaskDeliverable::with(['task', 'creator'])
                ->whereHas('task', function ($q) use ($user) {
                    $q->where(function($query) use ($user) {
                        $query->where('created_by', $user->id)
                              ->orWhereHas('users', function($userQuery) use ($user) {
                                  $userQuery->where('users.id', $user->id);
                              });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($deliverable) {
                    return [
                        'id' => $deliverable->id,
                        'title' => $deliverable->title,
                        'task_title' => $deliverable->task->title,
                        'created_by' => [
                            'first_name' => $deliverable->creator->first_name,
                            'last_name' => $deliverable->creator->last_name,
                        ],
                        'created_at' => $deliverable->created_at,
                    ];
                });

            // Get task statistics for charts (filtered for requester's tasks)
            $taskStatistics = $this->getRequesterTaskStatistics($user, $dateRange);
            
            // Get task completion trend for charts (filtered for requester's tasks)
            $taskTrend = $this->getRequesterTaskCompletionTrend($user, $dateRange);

            // Get overdue tasks list
            $overdueTasksList = Task::with(['users', 'status', 'priority', 'project.clients'])
                ->where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->where('end_date', '<', Carbon::now())
                ->where('status_id', '!=', $completedStatusId)
                ->orderBy('end_date', 'asc')
                ->limit(10)
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'end_date' => $task->end_date,
                        'status' => $task->status ? [
                            'id' => $task->status->id,
                            'title' => $task->status->title,
                        ] : null,
                        'priority' => $task->priority ? [
                            'id' => $task->priority->id,
                            'title' => $task->priority->title,
                        ] : null,
                        'project' => $task->project ? [
                            'id' => $task->project->id,
                            'title' => $task->project->title,
                        ] : null,
                        'clients' => $task->project && $task->project->clients ? $task->project->clients->map(function ($client) {
                            return [
                                'id' => $client->id,
                                'first_name' => $client->first_name,
                                'last_name' => $client->last_name,
                                'name' => $client->name,
                            ];
                        }) : [],
                        'users' => $task->users ? $task->users->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'first_name' => $user->first_name,
                                'last_name' => $user->last_name,
                            ];
                        }) : [],
                        'created_at' => $task->created_at,
                    ];
                });

            // Get user activity (filtered for requester's tasks)
            $userActivity = $this->getRequesterUserActivity($user, $dateRange);

            $dashboardData = [
                'overview' => [
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'pending_tasks' => $pendingTasks,
                    'overdue_tasks' => $overdueTasks,
                    'total_projects' => $totalProjects,
                ],
                'recent_tasks' => $recentTasks,
                'recent_projects' => $recentProjects,
                'recent_deliverables' => $recentDeliverables,
                'overdue_tasks' => $overdueTasksList,
                'task_statistics' => $taskStatistics,
                'task_trend' => $taskTrend,
                'user_activity' => $userActivity,
            ];

            return $this->sendResponse($dashboardData, 'Requester dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving requester dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get task statistics for requester (filtered for their tasks)
     */
    private function getRequesterTaskStatistics($user, $dateRange = null)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        // Base query for requester's tasks (created OR assigned)
        $taskQuery = Task::where(function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhereHas('users', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        });
        
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $taskQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        // Get status distribution
        $stats = $taskQuery->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get();

        $statusCounts = [];
        foreach ($stats as $stat) {
            $statusCounts[$stat->status_id] = $stat->count;
        }

        // Get completed this week
        $completedThisWeek = $completedStatusId ? (clone $taskQuery)->where('status_id', $completedStatusId)
            ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count() : 0;
            
        // Get overdue tasks
        $overdue = (clone $taskQuery)->where('end_date', '<', Carbon::now())
            ->where('status_id', '!=', $completedStatusId)
            ->count();
        
        return [
            'by_status' => $statusCounts,
            'completed_this_week' => $completedThisWeek,
            'overdue' => $overdue,
        ];
    }

    /**
     * Get task completion trend for requester (filtered for their tasks)
     */
    private function getRequesterTaskCompletionTrend($user, $dateRange = null)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        $trend = [];
        
        // Determine the number of days to show based on date range
        $daysToShow = 7; // Default
        if ($dateRange) {
            if ($dateRange['start']) {
                $daysToShow = min(30, Carbon::now()->diffInDays($dateRange['start']) + 1);
            }
        }
        
        for ($i = $daysToShow - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $query = Task::where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->where('status_id', $completedStatusId)
                ->whereDate('updated_at', $date);
            
            // Apply date range filtering if provided
            if ($dateRange && $dateRange['start']) {
                $query->where('created_at', '>=', $dateRange['start']);
            }
            
            $count = $completedStatusId ? $query->count() : 0;
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'completed_tasks' => $count,
            ];
        }

        return $trend;
    }

    /**
     * Get task statistics for tasker (filtered for their tasks)
     */
    private function getTaskerTaskStatistics($user, $dateRange = null)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        // Base query for tasker's tasks (assigned only)
        $taskQuery = Task::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        });
        
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $taskQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        // Get status distribution
        $stats = $taskQuery->select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get();

        $statusCounts = [];
        foreach ($stats as $stat) {
            $statusCounts[$stat->status_id] = $stat->count;
        }

        // Get completed this week
        $completedThisWeek = $completedStatusId ? (clone $taskQuery)->where('status_id', $completedStatusId)
            ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count() : 0;
            
        // Get overdue tasks
        $overdue = (clone $taskQuery)->where('end_date', '<', Carbon::now())
            ->where('status_id', '!=', $completedStatusId)
            ->count();
        
        return [
            'by_status' => $statusCounts,
            'completed_this_week' => $completedThisWeek,
            'overdue' => $overdue,
        ];
    }

    /**
     * Get task completion trend for tasker (filtered for their tasks)
     */
    private function getTaskerTaskCompletionTrend($user, $dateRange = null)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        $trend = [];
        
        // Determine the number of days to show based on date range
        $daysToShow = 7; // Default
        if ($dateRange) {
            if ($dateRange['start']) {
                $daysToShow = min(30, Carbon::now()->diffInDays($dateRange['start']) + 1);
            }
        }
        
        for ($i = $daysToShow - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $query = Task::whereHas('users', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->where('status_id', $completedStatusId)
                ->whereDate('updated_at', $date);
            
            // Apply date range filtering if provided
            if ($dateRange && $dateRange['start']) {
                $query->where('created_at', '>=', $dateRange['start']);
            }
            
            $count = $completedStatusId ? $query->count() : 0;
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'completed_tasks' => $count,
            ];
        }

        return $trend;
    }

    /**
     * Get user activity for tasker (filtered for their tasks)
     */
    private function getTaskerUserActivity($user, $dateRange = null)
    {
        // Get recent task updates for tasks assigned to this tasker
        $recentActivityQuery = Task::with(['users', 'status', 'priority', 'project'])
            ->whereHas('users', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
            
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $recentActivityQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        $recentActivity = $recentActivityQuery
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'action' => 'Task updated',
                    'status' => $task->status ? $task->status->title : 'Unknown',
                    'priority' => $task->priority ? $task->priority->title : 'Unknown',
                    'project' => $task->project ? $task->project->title : 'Unknown',
                    'updated_at' => $task->updated_at,
                ];
            });

        return $recentActivity;
    }

    /**
     * Get user activity for requester (filtered for their tasks)
     */
    private function getRequesterUserActivity($user, $dateRange = null)
    {
        // Get recent task updates for tasks created or assigned to this requester
        $recentActivityQuery = Task::with(['users', 'status', 'priority', 'project'])
            ->where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            });
            
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $recentActivityQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        $recentActivity = $recentActivityQuery
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'action' => 'Task updated',
                    'status' => $task->status ? $task->status->title : 'Unknown',
                    'priority' => $task->priority ? $task->priority->title : 'Unknown',
                    'project' => $task->project ? $task->project->title : 'Unknown',
                    'updated_at' => $task->updated_at,
                ];
            });

        return $recentActivity;
    }

    /**
     * Get overdue tasks
     */
    private function getOverdueTasks($user, $dateRange = null)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        // Base query for overdue tasks
        $overdueQuery = Task::with(['users', 'status', 'priority', 'project.clients', 'deliverables'])
            ->where('end_date', '<', Carbon::now())
            ->where('status_id', '!=', $completedStatusId);
            
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $overdueQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        // Apply role-based filtering
        if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
            // Admins and sub-admins see all overdue tasks (no additional filtering)
        } elseif ($user->hasRole('Requester')) {
            // Requesters only see overdue tasks they created OR are assigned to
            $overdueQuery->where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            });
        } elseif ($user->hasRole('Tasker')) {
            // Taskers only see overdue tasks they're assigned to
            $overdueQuery->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        
        $overdueTasks = $overdueQuery
            ->orderBy('end_date', 'asc') // Most overdue first
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'end_date' => $task->end_date,
                    'status' => $task->status ? [
                        'id' => $task->status->id,
                        'title' => $task->status->title,
                    ] : null,
                    'priority' => $task->priority ? [
                        'id' => $task->priority->id,
                        'title' => $task->priority->title,
                    ] : null,
                    'project' => $task->project ? [
                        'id' => $task->project->id,
                        'title' => $task->project->title,
                    ] : null,
                    'clients' => $task->project && $task->project->clients ? $task->project->clients->map(function ($client) {
                        return [
                            'id' => $client->id,
                            'first_name' => $client->first_name,
                            'last_name' => $client->last_name,
                            'name' => $client->name,
                        ];
                    }) : [],
                    'users' => $task->users ? $task->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                        ];
                    }) : [],
                    'deliverables_count' => $task->deliverables ? $task->deliverables->count() : 0,
                    'created_at' => $task->created_at,
                ];
            });

        return $overdueTasks;
    }

    /**
     * Get rejected tasks trend for the last 7 days
     */
    private function getRejectedTasksTrend($dateRange = null)
    {
        $rejectedStatus = Status::where('title', 'Rejected')->first();
        $rejectedStatusId = $rejectedStatus ? $rejectedStatus->id : null;
        
        $trend = [];
        
        // Determine the number of days to show based on date range
        $daysToShow = 7; // Default
        if ($dateRange) {
            if ($dateRange['start']) {
                $daysToShow = min(30, Carbon::now()->diffInDays($dateRange['start']) + 1);
            }
        }
        
        for ($i = $daysToShow - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $query = Task::where('status_id', $rejectedStatusId)
                ->whereDate('updated_at', $date);
            
            // Apply date range filtering if provided
            if ($dateRange && $dateRange['start']) {
                $query->where('created_at', '>=', $dateRange['start']);
            }
            
            $count = $rejectedStatusId ? $query->count() : 0;
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'rejected_tasks' => $count,
            ];
        }
        
        return $trend;
    }

    /**
     * Get tasks completed with unchecked checklists
     */
    private function getTasksWithUncheckedChecklists($dateRange = null)
    {
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        if (!$completedStatusId) {
            return [
                'total_completed_with_checklists' => 0,
                'completed_with_unchecked_items' => 0,
                'percentage_with_unchecked' => 0,
                'tasks_list' => []
            ];
        }

        // Get completed tasks that have checklists
        $completedTasksWithChecklistsQuery = Task::where('status_id', $completedStatusId)
            ->whereHas('template.briefchecks');
            
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $completedTasksWithChecklistsQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        $completedTasksWithChecklists = $completedTasksWithChecklistsQuery
            ->with(['template.briefchecks', 'checklistAnswers'])
            ->get();

        $totalCompletedWithChecklists = $completedTasksWithChecklists->count();
        $completedWithUncheckedItems = 0;
        $tasksWithUnchecked = [];

        foreach ($completedTasksWithChecklists as $task) {
            if ($task->template && $task->template->briefchecks && $task->template->briefchecks->count() > 0) {
                $checklist = $task->template->briefchecks->first();
                $checklistItems = $checklist->checklist ?? [];
                $checkedItems = $task->checklistAnswers ? $task->checklistAnswers->where('completed', true)->count() : 0;
                
                if ($checkedItems < count($checklistItems)) {
                    $completedWithUncheckedItems++;
                    $tasksWithUnchecked[] = [
                        'id' => $task->id,
                        'title' => $task->title,
                        'total_items' => count($checklistItems),
                        'checked_items' => $checkedItems,
                        'unchecked_items' => count($checklistItems) - $checkedItems,
                        'completion_date' => $task->updated_at,
                    ];
                }
            }
        }

        $percentageWithUnchecked = $totalCompletedWithChecklists > 0 
            ? round(($completedWithUncheckedItems / $totalCompletedWithChecklists) * 100, 1)
            : 0;

        return [
            'total_completed_with_checklists' => $totalCompletedWithChecklists,
            'completed_with_unchecked_items' => $completedWithUncheckedItems,
            'percentage_with_unchecked' => $percentageWithUnchecked,
            'tasks_list' => array_slice($tasksWithUnchecked, 0, 10) // Limit to 10 tasks
        ];
    }

    /**
     * Get average time to complete tasks
     */
    private function getAverageTaskCompletionTime($dateRange = null)
    {
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        if (!$completedStatusId) {
            return [
                'average_days' => 0,
                'average_hours' => 0,
                'total_completed_tasks' => 0,
                'fastest_completion' => 0,
                'slowest_completion' => 0
            ];
        }

        // Get completed tasks with their completion time
        $completedTasksQuery = Task::where('status_id', $completedStatusId)
            ->whereNotNull('updated_at');
            
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $completedTasksQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        $completedTasks = $completedTasksQuery->get();

        $totalCompleted = $completedTasks->count();
        $totalDays = 0;
        $completionTimes = [];

        foreach ($completedTasks as $task) {
            $createdAt = Carbon::parse($task->created_at);
            $completedAt = Carbon::parse($task->updated_at);
            $daysToComplete = $createdAt->diffInDays($completedAt);
            $hoursToComplete = $createdAt->diffInHours($completedAt);
            
            $totalDays += $daysToComplete;
            $completionTimes[] = $hoursToComplete;
        }

        $averageDays = $totalCompleted > 0 ? round($totalDays / $totalCompleted, 1) : 0;
        $averageHours = $totalCompleted > 0 ? round(array_sum($completionTimes) / $totalCompleted, 1) : 0;
        $fastestCompletion = !empty($completionTimes) ? min($completionTimes) : 0;
        $slowestCompletion = !empty($completionTimes) ? max($completionTimes) : 0;

        return [
            'average_days' => $averageDays,
            'average_hours' => $averageHours,
            'total_completed_tasks' => $totalCompleted,
            'fastest_completion' => $fastestCompletion,
            'slowest_completion' => $slowestCompletion
        ];
    }

    /**
     * Get additional statistics
     */
    private function getAdditionalStatistics($dateRange = null)
    {
        $user = Auth::user();
        
        // Apply role-based filtering
        $taskQuery = Task::query();
        
        // Apply date filtering if provided
        if ($dateRange && $dateRange['start']) {
            $taskQuery->where('created_at', '>=', $dateRange['start']);
        }
        
        if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
            // Admins and sub-admins see all tasks
        } elseif ($user->hasRole('Requester')) {
            $taskQuery->where('created_by', $user->id);
        } elseif ($user->hasRole('Tasker')) {
            $taskQuery->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Get status IDs
        $completedStatus = Status::where('title', 'Completed')->first();
        $rejectedStatus = Status::where('title', 'Rejected')->first();
        $pendingStatus = Status::where('title', 'Pending')->first();
        $inProgressStatus = Status::where('title', 'In Progress')->first();

        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        $rejectedStatusId = $rejectedStatus ? $rejectedStatus->id : null;
        $pendingStatusId = $pendingStatus ? $pendingStatus->id : null;
        $inProgressStatusId = $inProgressStatus ? $inProgressStatus->id : null;

        // Calculate statistics
        $totalTasks = (clone $taskQuery)->count();
        $completedTasks = $completedStatusId ? (clone $taskQuery)->where('status_id', $completedStatusId)->count() : 0;
        $rejectedTasks = $rejectedStatusId ? (clone $taskQuery)->where('status_id', $rejectedStatusId)->count() : 0;
        $pendingTasks = $pendingStatusId ? (clone $taskQuery)->where('status_id', $pendingStatusId)->count() : 0;
        $inProgressTasks = $inProgressStatusId ? (clone $taskQuery)->where('status_id', $inProgressStatusId)->count() : 0;

        // Calculate percentages
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        $rejectionRate = $totalTasks > 0 ? round(($rejectedTasks / $totalTasks) * 100, 1) : 0;

        // Get tasks with deliverables
        $tasksWithDeliverables = (clone $taskQuery)->whereHas('deliverables')->count();
        $deliverableRate = $totalTasks > 0 ? round(($tasksWithDeliverables / $totalTasks) * 100, 1) : 0;

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'rejected_tasks' => $rejectedTasks,
            'pending_tasks' => $pendingTasks,
            'in_progress_tasks' => $inProgressTasks,
            'completion_rate' => $completionRate,
            'rejection_rate' => $rejectionRate,
            'tasks_with_deliverables' => $tasksWithDeliverables,
            'deliverable_rate' => $deliverableRate,
        ];
    }
}

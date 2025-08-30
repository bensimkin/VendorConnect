<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
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

            // Get basic counts with role-based filtering
            $totalTasksQuery = Task::query();
            
            if ($user->hasRole('Requester')) {
                $totalTasksQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('Tasker')) {
                $totalTasksQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
            
            $totalTasks = $totalTasksQuery->count();
            $totalUsers = User::count();
            $totalClients = Client::count();
            $totalProjects = Project::count();

            // Get task statistics
            $taskStats = $this->getTaskStatistics();
            
            // Get recent tasks with role-based filtering
            $recentTasksQuery = Task::with(['users', 'status', 'priority', 'taskType', 'template', 'deliverables']);
            
            // Apply role-based filtering for recent tasks
            if ($user->hasRole('Requester')) {
                // Requesters only see tasks they created
                $recentTasksQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('Tasker')) {
                // Taskers only see tasks they're assigned to
                $recentTasksQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
            // Admins and sub-admins see all tasks (no additional filtering)
            
            $recentTasks = $recentTasksQuery
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Add deliverables count to recent tasks
            $recentTasks->each(function ($task) {
                $task->deliverables_count = $task->deliverables ? $task->deliverables->count() : 0;
            });

            // Get user activity
            $userActivity = $this->getUserActivity();

            // Get task completion trend
            $taskTrend = $this->getTaskCompletionTrend();

            // Get project management data
            $projectManagement = $this->getProjectManagementData($user);

            // Get overdue tasks
            $overdueTasks = $this->getOverdueTasks($user);

            // Get all statuses for frontend mapping
            $statuses = Status::select('id', 'title')->get();

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
            ];

            return $this->sendResponse($dashboardData, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get task statistics
     */
    private function getTaskStatistics()
    {
        $user = Auth::user();
        
        // Apply role-based filtering to task statistics
        $taskQuery = Task::query();
        
        if ($user->hasRole('Requester')) {
            // Requesters only see tasks they created
            $taskQuery->where('created_by', $user->id);
                    } elseif ($user->hasRole('Tasker')) {
                // Taskers only see tasks they're assigned to
                $taskQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
        }
        // Admins and sub-admins see all tasks (no additional filtering)
        
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
    private function getUserActivity()
    {
        $user = Auth::user();
        
        $userQuery = User::withCount(['userTask as recent_tasks' => function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
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
    private function getTaskCompletionTrend()
    {
        // Get status IDs dynamically
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = $completedStatusId ? Task::where('status_id', $completedStatusId)
                ->whereDate('updated_at', $date)
                ->count() : 0;
            
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
    private function getProjectManagementData($user)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;

        $projectQuery = Project::with(['status', 'clients'])
            ->withCount(['tasks as total_tasks'])
            ->withCount(['tasks as active_tasks' => function ($query) use ($completedStatusId) {
                if ($completedStatusId) {
                    $query->where('status_id', '!=', $completedStatusId);
                }
            }])
            ->withCount(['tasks as overdue_tasks' => function ($query) use ($completedStatusId) {
                $query->where('end_date', '<', Carbon::now());
                if ($completedStatusId) {
                    $query->where('status_id', '!=', $completedStatusId);
                }
            }])
            ->withCount(['tasks as completed_this_week_tasks' => function ($query) use ($completedStatusId) {
                if ($completedStatusId) {
                    $query->where('status_id', $completedStatusId)
                        ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                }
            }]);

        // Apply role-based filtering
        if ($user->hasRole('Requester')) {
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
        // Admins and sub-admins see all projects

        return $projectQuery->orderBy('updated_at', 'desc')->limit(10)->get();
    }

    /**
     * Get tasker dashboard data
     */
    public function taskerDashboard()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('Tasker')) {
                return $this->sendError('Unauthorized access', [], 403);
            }

            // Get completed status ID
            $completedStatus = Status::where('title', 'Completed')->first();
            $completedStatusId = $completedStatus ? $completedStatus->id : null;

            // Overview statistics
            $totalTasks = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->count();

            $completedTasks = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->where('status_id', $completedStatusId)->count();

            $pendingTasks = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->where('status_id', '!=', $completedStatusId)->count();

            $overdueTasks = Task::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->where('end_date', '<', Carbon::now())
              ->where('status_id', '!=', $completedStatusId)->count();

            // Recent tasks
            $recentTasks = Task::with(['status', 'priority', 'project', 'template'])
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

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
            ];

            return $this->sendResponse($dashboardData, 'Tasker dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving tasker dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get requester dashboard data
     */
    public function requesterDashboard()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('Requester')) {
                return $this->sendError('Unauthorized access', [], 403);
            }

            // Get completed status ID
            $completedStatus = Status::where('title', 'Completed')->first();
            $completedStatusId = $completedStatus ? $completedStatus->id : null;

            // Overview statistics - include both created and assigned tasks/projects
            $totalTasks = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->count();
            
            $completedTasks = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->where('status_id', $completedStatusId)->count();
            
            $pendingTasks = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->where('status_id', '!=', $completedStatusId)->count();
            
            $overdueTasks = Task::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->where('end_date', '<', Carbon::now())
              ->where('status_id', '!=', $completedStatusId)->count();
            
            $totalProjects = Project::where(function($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhereHas('users', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->count();

            // Recent tasks - include both created and assigned tasks
            $recentTasks = Task::with(['status', 'priority', 'project', 'users', 'template'])
                ->where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

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
            $taskStatistics = $this->getRequesterTaskStatistics($user);
            
            // Get task completion trend for charts (filtered for requester's tasks)
            $taskTrend = $this->getRequesterTaskCompletionTrend($user);

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
                'task_statistics' => $taskStatistics,
                'task_trend' => $taskTrend,
            ];

            return $this->sendResponse($dashboardData, 'Requester dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving requester dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Get task statistics for requester (filtered for their tasks)
     */
    private function getRequesterTaskStatistics($user)
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
    private function getRequesterTaskCompletionTrend($user)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = $completedStatusId ? Task::where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->where('status_id', $completedStatusId)
                ->whereDate('updated_at', $date)
                ->count() : 0;
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'completed_tasks' => $count,
            ];
        }

        return $trend;
    }

    /**
     * Get overdue tasks
     */
    private function getOverdueTasks($user)
    {
        // Get completed status ID
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        // Base query for overdue tasks
        $overdueQuery = Task::with(['users', 'status', 'priority', 'project.clients', 'deliverables'])
            ->where('end_date', '<', Carbon::now())
            ->where('status_id', '!=', $completedStatusId);
        
        // Apply role-based filtering
        if ($user->hasRole('Requester')) {
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
        // Admins and sub-admins see all overdue tasks (no additional filtering)
        
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
}

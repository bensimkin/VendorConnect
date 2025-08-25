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
            
            if ($user->hasRole('requester')) {
                $totalTasksQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('tasker')) {
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
            $recentTasksQuery = Task::with(['users', 'status', 'priority', 'taskType']);
            
            // Apply role-based filtering for recent tasks
            if ($user->hasRole('requester')) {
                // Requesters only see tasks they created
                $recentTasksQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('tasker')) {
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

            // Get user activity
            $userActivity = $this->getUserActivity();

            // Get task completion trend
            $taskTrend = $this->getTaskCompletionTrend();

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
                'user_activity' => $userActivity,
                'task_trend' => $taskTrend,
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
        
        if ($user->hasRole('requester')) {
            // Requesters only see tasks they created
            $taskQuery->where('created_by', $user->id);
        } elseif ($user->hasRole('tasker')) {
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
        return User::withCount(['userTask as recent_tasks' => function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            }])
            ->orderBy('recent_tasks', 'desc')
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'email']);
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
}

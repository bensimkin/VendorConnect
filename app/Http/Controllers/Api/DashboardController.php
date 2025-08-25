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

            // Get basic counts (removed workspace filtering for single-tenant)
            $totalTasks = Task::count();
            $totalUsers = User::count();
            $totalClients = Client::count();
            $totalProjects = Project::count();

            // Get task statistics
            $taskStats = $this->getTaskStatistics();
            
            // Get recent tasks
            $recentTasks = Task::with(['users', 'status', 'priority', 'taskType'])
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
        $stats = Task::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get();

        $statusCounts = [];
        foreach ($stats as $stat) {
            $statusCounts[$stat->status_id] = $stat->count;
        }

        // Get status IDs dynamically
        $completedStatus = Status::where('title', 'Completed')->first();
        $completedStatusId = $completedStatus ? $completedStatus->id : null;
        
        return [
            'by_status' => $statusCounts,
            'completed_this_week' => $completedStatusId ? Task::where('status_id', $completedStatusId)
                ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count() : 0,
            'overdue' => Task::where('end_date', '<', Carbon::now())
                ->where('status_id', '!=', $completedStatusId) // Not completed
                ->count(),
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

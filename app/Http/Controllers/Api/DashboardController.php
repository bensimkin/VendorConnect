<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
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

        return [
            'by_status' => $statusCounts,
            'completed_this_week' => Task::where('status_id', 3) // Assuming 3 is completed status
                ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'overdue' => Task::where('end_date', '<', Carbon::now())
                ->where('status_id', '!=', 3) // Not completed
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
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Task::where('status_id', 3) // Completed status
                ->whereDate('updated_at', $date)
                ->count();
            
            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'completed_tasks' => $count,
            ];
        }

        return $trend;
    }
}

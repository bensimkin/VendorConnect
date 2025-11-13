<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Admin;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OwnerController extends BaseController
{
    /**
     * Check if user is owner
     */
    private function isOwner()
    {
        $user = Auth::user();
        return $user && $user->hasRole('owner');
    }

    /**
     * Get all companies with statistics
     */
    public function getAllCompanies(Request $request)
    {
        try {
            if (!$this->isOwner()) {
                return $this->sendError('Unauthorized: Owner access required', [], 403);
            }

            $companies = Admin::with('user')->get()->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'company_name' => $admin->company_name ?: 'Unnamed Company',
                    'company_email' => $admin->company_email,
                    'owner' => [
                        'id' => $admin->user->id,
                        'name' => $admin->user->first_name . ' ' . $admin->user->last_name,
                        'email' => $admin->user->email,
                    ],
                    'created_at' => $admin->created_at,
                    'stats' => [
                        'projects' => Project::where('admin_id', $admin->id)->count(),
                        'tasks' => Task::where('admin_id', $admin->id)->count(),
                        'clients' => Client::where('admin_id', $admin->id)->count(),
                        'users' => \App\Models\TeamMember::where('admin_id', $admin->id)->count() + 1, // +1 for owner
                    ],
                ];
            });

            return $this->sendResponse($companies, 'Companies retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving companies: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed analytics for a specific company
     */
    public function getCompanyAnalytics(Request $request, $adminId)
    {
        try {
            if (!$this->isOwner()) {
                return $this->sendError('Unauthorized: Owner access required', [], 403);
            }

            $admin = Admin::with('user')->find($adminId);
            
            if (!$admin) {
                return $this->sendNotFound('Company not found');
            }

            // Get date range (default last 30 days)
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());

            // Overview stats
            $totalProjects = Project::where('admin_id', $adminId)->count();
            $totalTasks = Task::where('admin_id', $adminId)->count();
            $totalClients = Client::where('admin_id', $adminId)->count();
            $totalUsers = \App\Models\TeamMember::where('admin_id', $adminId)->count() + 1;

            // Recent activity
            $recentProjects = Project::where('admin_id', $adminId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            $recentTasks = Task::where('admin_id', $adminId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Task status breakdown
            $tasksByStatus = Task::where('admin_id', $adminId)
                ->select('status_id', DB::raw('count(*) as count'))
                ->groupBy('status_id')
                ->with('status:id,title')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status ? $item->status->title : 'Unknown',
                        'count' => $item->count
                    ];
                });

            // Get user IDs for this company (owner + team members)
            $companyUserIds = \App\Models\TeamMember::where('admin_id', $adminId)
                ->pluck('user_id')
                ->push($admin->user_id) // Add owner
                ->unique()
                ->values()
                ->all();

            // Active user metrics
            $activeToday = \App\Models\UserSession::whereNull('logout_at')
                ->where('last_activity_at', '>=', Carbon::now()->subHours(24))
                ->whereIn('user_id', $companyUserIds)
                ->distinct('user_id')
                ->count('user_id');

            $activeLast7Days = \App\Models\UserSession::where('last_activity_at', '>=', Carbon::now()->subDays(7))
                ->whereIn('user_id', $companyUserIds)
                ->distinct('user_id')
                ->count('user_id');

            $activeLast30Days = \App\Models\UserSession::where('last_activity_at', '>=', Carbon::now()->subDays(30))
                ->whereIn('user_id', $companyUserIds)
                ->distinct('user_id')
                ->count('user_id');

            // Recent sessions (last 7 days)
            $totalSessions = \App\Models\UserSession::whereBetween('login_at', [Carbon::now()->subDays(7), Carbon::now()])
                ->whereIn('user_id', $companyUserIds)
                ->count();

            // Average session duration (last 7 days)
            $avgSessionDuration = \App\Models\UserSession::whereBetween('login_at', [Carbon::now()->subDays(7), Carbon::now()])
                ->whereNotNull('duration_seconds')
                ->whereIn('user_id', $companyUserIds)
                ->avg('duration_seconds');

            // Last activity timestamp
            $lastActivity = Task::where('admin_id', $adminId)
                ->orderBy('updated_at', 'desc')
                ->first();

            return $this->sendResponse([
                'company' => [
                    'id' => $admin->id,
                    'company_name' => $admin->company_name ?: 'Unnamed Company',
                    'company_email' => $admin->company_email,
                    'created_at' => $admin->created_at,
                    'owner' => [
                        'name' => $admin->user->first_name . ' ' . $admin->user->last_name,
                        'email' => $admin->user->email,
                    ],
                ],
                'overview' => [
                    'total_projects' => $totalProjects,
                    'total_tasks' => $totalTasks,
                    'total_clients' => $totalClients,
                    'total_users' => $totalUsers,
                ],
                'period_activity' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'projects_created' => $recentProjects,
                    'tasks_created' => $recentTasks,
                ],
                'tasks_by_status' => $tasksByStatus,
                'activity_metrics' => [
                    'active_users_today' => $activeToday,
                    'active_users_7_days' => $activeLast7Days,
                    'active_users_30_days' => $activeLast30Days,
                    'total_sessions_7_days' => $totalSessions,
                    'avg_session_duration_seconds' => $avgSessionDuration ? round($avgSessionDuration, 0) : 0,
                    'last_activity_at' => $lastActivity ? $lastActivity->updated_at : null,
                ],
            ], 'Company analytics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving company analytics: ' . $e->getMessage());
        }
    }

    /**
     * Get platform-wide statistics
     */
    public function getPlatformStats(Request $request)
    {
        try {
            if (!$this->isOwner()) {
                return $this->sendError('Unauthorized: Owner access required', [], 403);
            }

            $totalCompanies = Admin::count();
            $totalUsers = User::count();
            $totalProjects = Project::count();
            $totalTasks = Task::count();
            $totalClients = Client::count();

            // Companies created by month (last 12 months)
            $companiesByMonth = Admin::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('count(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            return $this->sendResponse([
                'overview' => [
                    'total_companies' => $totalCompanies,
                    'total_users' => $totalUsers,
                    'total_projects' => $totalProjects,
                    'total_tasks' => $totalTasks,
                    'total_clients' => $totalClients,
                ],
                'companies_by_month' => $companiesByMonth,
            ], 'Platform statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving platform stats: ' . $e->getMessage());
        }
    }
}


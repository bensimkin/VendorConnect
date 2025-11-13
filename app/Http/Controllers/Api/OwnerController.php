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

            // Recent logins (last 30 days)
            $recentLogins = \App\Models\TeamMember::where('admin_id', $adminId)
                ->with(['user' => function($q) {
                    $q->select('id', 'first_name', 'last_name', 'email', 'last_login_at')
                      ->whereNotNull('last_login_at')
                      ->where('last_login_at', '>=', Carbon::now()->subDays(30));
                }])
                ->get()
                ->pluck('user')
                ->filter()
                ->count();

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
                'active_users_last_30_days' => $recentLogins,
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


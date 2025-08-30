<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Global search across multiple entities
     */
    public function globalSearch(Request $request)
    {
        try {
            $user = Auth::user();
            $query = $request->get('q', '');
            
            if (empty($query) || strlen($query) < 2) {
                return $this->sendResponse([
                    'clients' => [],
                    'projects' => [],
                    'tasks' => [],
                    'portfolio' => [],
                    'total_results' => 0
                ], 'Search results');
            }

            $searchTerm = '%' . $query . '%';
            $results = [];

            // Search Clients (only for Admin users)
            if ($user->hasRole(['Admin', 'sub admin'])) {
                $clients = Client::where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                      ->orWhere('first_name', 'like', $searchTerm)
                      ->orWhere('last_name', 'like', $searchTerm)
                      ->orWhere('email', 'like', $searchTerm)
                      ->orWhere('company', 'like', $searchTerm)
                      ->orWhere('phone', 'like', $searchTerm);
                })
                ->with(['projects', 'portfolios'])
                ->limit(5)
                ->get()
                ->map(function($client) {
                    return [
                        'id' => $client->id,
                        'type' => 'client',
                        'title' => $client->name ?: ($client->first_name . ' ' . $client->last_name),
                        'subtitle' => $client->company ?: $client->email,
                        'url' => '/clients/' . $client->id,
                        'icon' => 'Building2',
                        'description' => $client->email . ($client->phone ? ' • ' . $client->phone : '')
                    ];
                });
                $results['clients'] = $clients;
            } else {
                $results['clients'] = collect();
            }

            // Search Projects (with role-based filtering)
            $projectQuery = Project::where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('note', 'like', $searchTerm);
            });

            // Apply role-based filtering
            if ($user->hasRole('Requester')) {
                $projectQuery->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('tasks', function($subQ) use ($user) {
                          $subQ->where('created_by', $user->id);
                      });
                });
            } elseif ($user->hasRole('Tasker')) {
                $projectQuery->whereHas('tasks', function($q) use ($user) {
                    $q->whereHas('users', function($subQ) use ($user) {
                        $subQ->where('users.id', $user->id);
                    });
                });
            }
            // Admins and sub-admins see all projects

            $projects = $projectQuery->with(['status', 'clients'])
                ->limit(5)
                ->get()
                ->map(function($project) {
                    return [
                        'id' => $project->id,
                        'type' => 'project',
                        'title' => $project->title,
                        'subtitle' => $project->status?->title ?: 'No Status',
                        'url' => '/projects/' . $project->id,
                        'icon' => 'FolderOpen',
                        'description' => $project->description ?: 'No description'
                    ];
                });
            $results['projects'] = $projects;

            // Search Tasks (with role-based filtering)
            $taskQuery = Task::where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('note', 'like', $searchTerm);
            });

            // Apply role-based filtering
            if ($user->hasRole('Requester')) {
                $taskQuery->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('users', function($subQ) use ($user) {
                          $subQ->where('users.id', $user->id);
                      });
                });
            } elseif ($user->hasRole('Tasker')) {
                $taskQuery->whereHas('users', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
            // Admins and sub-admins see all tasks

            $tasks = $taskQuery->with(['status', 'priority', 'project', 'users'])
                ->limit(5)
                ->get()
                ->map(function($task) {
                    return [
                        'id' => $task->id,
                        'type' => 'task',
                        'title' => $task->title,
                        'subtitle' => $task->status?->title ?: 'No Status',
                        'url' => '/tasks/' . $task->id,
                        'icon' => 'CheckSquare',
                        'description' => $task->description ?: 'No description',
                        'priority' => $task->priority?->title,
                        'project' => $task->project?->title
                    ];
                });
            $results['tasks'] = $tasks;

            // Search Portfolio Items (with role-based filtering)
            $portfolioQuery = Portfolio::where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('content', 'like', $searchTerm);
            });

            // Apply role-based filtering
            if ($user->hasRole('Requester')) {
                $portfolioQuery->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('task', function($subQ) use ($user) {
                          $subQ->where('created_by', $user->id)
                               ->orWhereHas('users', function($subSubQ) use ($user) {
                                   $subSubQ->where('users.id', $user->id);
                               });
                      });
                });
            } elseif ($user->hasRole('Tasker')) {
                $portfolioQuery->whereHas('task', function($q) use ($user) {
                    $q->whereHas('users', function($subQ) use ($user) {
                        $subQ->where('users.id', $user->id);
                    });
                });
            }
            // Admins and sub-admins see all portfolio items

            $portfolio = $portfolioQuery->with(['client', 'task'])
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'portfolio',
                        'title' => $item->title,
                        'subtitle' => $item->client?->name ?: 'No Client',
                        'url' => '/portfolio/' . $item->id,
                        'icon' => 'Briefcase',
                        'description' => $item->description ?: 'No description',
                        'client' => $item->client?->name,
                        'task' => $item->task?->title
                    ];
                });
            $results['portfolio'] = $portfolio;

            // Calculate total results
            $totalResults = $results['clients']->count() + 
                           $results['projects']->count() + 
                           $results['tasks']->count() + 
                           $results['portfolio']->count();

            $results['total_results'] = $totalResults;

            return $this->sendResponse($results, 'Search results retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Error performing search: ' . $e->getMessage());
        }
    }
}

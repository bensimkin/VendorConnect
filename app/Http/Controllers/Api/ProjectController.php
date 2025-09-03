<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Project;
use App\Models\User;
use App\Models\Client;
use App\Models\Setting;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProjectController extends BaseController
{
    /**
     * Get all projects with pagination
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Project::with([
                'users', 
                'status', 
                'clients',
                'createdBy:id,first_name,last_name,email',
                'tasks' => function($q) use ($user) {
                    $q->with([
                        'users:id,first_name,last_name,email,phone',
                        'status:id,title',
                        'priority:id,title',
                        'createdBy:id,first_name,last_name,email',
                        'deliverables'
                    ]);
                    
                    // Role-based filtering for tasks within projects
                    if ($user->hasRole('Requester')) {
                        // Requesters see tasks they created OR are assigned to
                        $q->where(function($subQ) use ($user) {
                            $subQ->where('created_by', $user->id)
                                  ->orWhereHas('users', function($taskUserQ) use ($user) {
                                      $taskUserQ->where('users.id', $user->id);
                                  });
                        });
                    } elseif ($user->hasRole('Tasker')) {
                        // Taskers only see tasks they're assigned to
                        $q->whereHas('users', function($subQ) use ($user) {
                            $subQ->where('users.id', $user->id);
                        });
                    }
                    // Admins and sub-admins see all tasks (no additional filtering)
                }
            ]);
            // Removed workspace filtering for single-tenant system

            // Role-based filtering
            if ($user->hasRole('Requester')) {
                // Requesters only see projects they created
                $query->where('created_by', $user->id);
            } elseif ($user->hasRole('Tasker')) {
                // Taskers only see projects they're involved in via assigned tasks
                $query->whereHas('tasks', function($q) use ($user) {
                    $q->whereHas('users', function($subQ) use ($user) {
                        $subQ->where('users.id', $user->id);
                    });
                });
            }
            // Admins and sub-admins see all projects (no additional filtering)

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            } elseif ($request->has('status')) {
                // Fallback for status by title
                $query->whereHas('status', function ($q) use ($request) {
                    $q->where('title', 'like', "%{$request->status}%");
                });
            }

            if ($request->has('user_id')) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            }

            if ($request->has('client_id')) {
                // Filter by client through project-client relationship
                $query->whereHas('clients', function ($q) use ($request) {
                    $q->where('clients.id', $request->client_id);
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Support returning all records without pagination for dropdowns
            if ($request->get('per_page') === 'all') {
                $projects = $query->withCount(['tasks as tasks_count'])
                    ->withCount(['tasks as completed_tasks' => function($q) {
                        $q->where('status_id', 17);
                    }])
                    ->withCount(['users as team_members_count'])
                    ->get();
            } else {
                $projects = $query->withCount(['tasks as tasks_count'])
                    ->withCount(['tasks as completed_tasks' => function($q) {
                        $q->where('status_id', 17); // Completed status ID
                    }])
                    ->withCount(['users as team_members_count'])
                    ->paginate($request->get('per_page', 15));
            }

            // Add task users count to each project
            $projectItems = is_object($projects) && method_exists($projects, 'items') ? $projects->items() : (is_iterable($projects) ? $projects : []);
            foreach ($projectItems as $project) {
                $taskUsersCount = User::whereHas('tasks', function($query) use ($project) {
                    $query->where('project_id', $project->id);
                })->count();
                
                $project->team_members_count += $taskUsersCount;
                
                // Calculate task counts based on filtered tasks (not all project tasks)
                $filteredTasks = $project->tasks;
                $project->total_tasks = $filteredTasks ? $filteredTasks->count() : 0;
                
                // Count completed tasks from filtered tasks
                $completedTasks = $filteredTasks ? $filteredTasks->where('status_id', 17)->count() : 0;
                $project->active_tasks = $project->total_tasks - $completedTasks;
                
                // Calculate overdue tasks from filtered tasks
                $overdueTasks = $filteredTasks ? $filteredTasks->filter(function($task) {
                    return $task->end_date && 
                           $task->end_date < now() && 
                           $task->status_id != 17; // Not completed
                })->count() : 0;
                $project->overdue_tasks = $overdueTasks;
                
                // Add deliverables count to tasks
                if ($project->tasks) {
                    $project->tasks->each(function ($task) {
                        $task->deliverables_count = $task->deliverables ? $task->deliverables->count() : 0;
                    });
                }
            }

            if ($request->get('per_page') === 'all') {
                return $this->sendResponse($projects, 'Projects retrieved successfully');
            }
            return $this->sendPaginatedResponse($projects, 'Projects retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving projects: ' . $e->getMessage());
        }
    }

    /**
     * Store a new project
     */
    public function store(Request $request)
    {
        try {
            // Check if multiple clients are allowed
            $allowMultipleClients = Setting::isEnabled('allow_multiple_clients_per_project', false);
            $requireProjectClient = Setting::isEnabled('require_project_client', true);
            $maxClientsPerProject = Setting::getValue('max_clients_per_project', 5);

            $validationRules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'sometimes|string|in:active,completed,on_hold,cancelled',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'exists:users,id',
                'budget' => 'nullable|numeric|min:0',
            ];

            if ($allowMultipleClients) {
                $validationRules['client_ids'] = 'nullable|array';
                $validationRules['client_ids.*'] = 'exists:clients,id';
                $validationRules['client_id'] = 'nullable|exists:clients,id';
                
                // Validate max clients if multiple clients are provided
                if ($request->has('client_ids') && count($request->client_ids) > $maxClientsPerProject) {
                    return $this->sendValidationError(['client_ids' => ["Maximum {$maxClientsPerProject} clients allowed per project."]]);
                }
            } else {
                $validationRules['client_id'] = 'required|exists:clients,id';
                $validationRules['client_ids'] = 'prohibited';
            }

            $validator = Validator::make($request->all(), $validationRules);

            // Custom validation to ensure at least one client is provided if required
            if ($requireProjectClient && !$request->has('client_id') && !$request->has('client_ids')) {
                return $this->sendValidationError(['client_id' => ['A client must be selected.']]);
            }

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $project = Project::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date ?: $request->start_date, // Use start_date as default if end_date is not provided
                'status_id' => $request->status_id ?? 20, // Use provided status_id or default to Active (20)
                'created_by' => $request->user()->id,
                'workspace_id' => 1, // Default workspace for single-tenant system
            ]);

            // Attach users
            if ($request->has('user_ids')) {
                $project->users()->attach($request->user_ids);
            }

            // Attach clients
            if ($request->filled('client_ids')) {
                // Multiple clients mode
                \Log::info('Attaching multiple clients to project', ['client_ids' => $request->client_ids, 'project_id' => $project->id]);
                $project->clients()->attach($request->client_ids, ['admin_id' => 1]);
            } elseif ($request->filled('client_id')) {
                // Single client mode
                \Log::info('Attaching single client to project', ['client_id' => $request->client_id, 'project_id' => $project->id]);
                $project->clients()->attach($request->client_id, ['admin_id' => 1]);
            } else {
                \Log::warning('No client data provided for project creation', ['project_id' => $project->id, 'request_data' => $request->all()]);
            }

            DB::commit();

            $project->load(['users', 'tasks', 'status', 'clients']);

            return $this->sendResponse($project, 'Project created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error creating project: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific project
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $project = Project::with([
                'users', 
                'status', 
                'clients',
                'createdBy:id,first_name,last_name,email',
                'tasks.status'
            ])
                ->find($id);

            if (!$project) {
                return $this->sendNotFound('Project not found');
            }

            // Get users assigned to tasks within this project
            $taskUsers = \App\Models\User::whereHas('tasks', function($query) use ($id) {
                $query->where('project_id', $id);
            })->get();

            // Merge direct project users with task users
            $allUsers = $project->users->merge($taskUsers)->unique('id');
            $project->setRelation('users', $allUsers);

            // Apply role-based data protection to project users
            if (!$user->hasRole(['admin', 'sub_admin'])) {
                // Remove sensitive data from users for requesters and taskers
                foreach ($project->users as $projectUser) {
                    unset($projectUser->email);
                    unset($projectUser->phone);
                    unset($projectUser->address);
                    unset($projectUser->city);
                    unset($projectUser->state);
                    unset($projectUser->country);
                    unset($projectUser->zip);
                    unset($projectUser->dob);
                }
            }

            return $this->sendResponse($project, 'Project retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving project: ' . $e->getMessage());
        }
    }

    /**
     * Get tasks for a specific project
     */
    public function getTasks($id)
    {
        try {
            $user = Auth::user();
            
            // Check if project exists
            $project = Project::find($id);
            if (!$project) {
                return $this->sendNotFound('Project not found');
            }

            // Build task query with role-based filtering
            $taskQuery = Task::with(['users', 'status', 'priority', 'taskType'])
                ->where('project_id', $id);

            // Apply role-based filtering
            if ($user->hasRole('Requester')) {
                // Requesters only see tasks they created
                $taskQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('Tasker')) {
                // Taskers only see tasks they're assigned to
                $taskQuery->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
            // Admins and sub-admins see all tasks

            $tasks = $taskQuery->orderBy('created_at', 'desc')->get();

            // Apply role-based data protection to task users
            if (!$user->hasRole(['admin', 'sub_admin'])) {
                // Remove sensitive data from assigned users for requesters and taskers
                foreach ($tasks as $task) {
                    if ($task->users) {
                        foreach ($task->users as $taskUser) {
                            unset($taskUser->email);
                            unset($taskUser->phone);
                        }
                    }
                }
            }

            return $this->sendResponse($tasks, 'Project tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving project tasks: ' . $e->getMessage());
        }
    }

    /**
     * Update a project
     */
    public function update(Request $request, $id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return $this->sendNotFound('Project not found');
            }

            // Check if multiple clients are allowed
            $allowMultipleClients = Setting::isEnabled('allow_multiple_clients_per_project', false);
            $maxClientsPerProject = Setting::getValue('max_clients_per_project', 5);

            $validationRules = [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status_id' => 'sometimes|exists:statuses,id',
                'budget' => 'nullable|numeric|min:0',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'exists:users,id',
            ];

            // Add client validation rules
            if ($allowMultipleClients) {
                $validationRules['client_ids'] = 'nullable|array';
                $validationRules['client_ids.*'] = 'exists:clients,id';
                $validationRules['client_id'] = 'nullable|exists:clients,id';
                
                // Validate max clients if multiple clients are provided
                if ($request->has('client_ids') && count($request->client_ids) > $maxClientsPerProject) {
                    return $this->sendValidationError(['client_ids' => ["Maximum {$maxClientsPerProject} clients allowed per project."]]);
                }
            } else {
                $validationRules['client_id'] = 'nullable|exists:clients,id';
                $validationRules['client_ids'] = 'prohibited';
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                \Log::error('Project update validation failed', [
                    'project_id' => $id,
                    'request_data' => $request->all(),
                    'validation_errors' => $validator->errors()->toArray()
                ]);
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $project->update($request->only([
                'title', 'description', 'start_date', 'end_date', 'status_id', 'budget'
            ]));

            // Sync users
            if ($request->has('user_ids')) {
                $project->users()->sync($request->user_ids);
            }

            // Handle client assignment - support both single client_id and client_ids array
            if ($request->filled('client_ids')) {
                // Multiple clients mode - sync with admin_id
                $clientData = [];
                foreach ($request->client_ids as $clientId) {
                    $clientData[$clientId] = ['admin_id' => 1];
                }
                $project->clients()->sync($clientData);
            } elseif ($request->filled('client_id')) {
                // Single client mode - sync with admin_id
                $project->clients()->sync([$request->client_id => ['admin_id' => 1]]);
            }

            DB::commit();

            $project->load(['users', 'tasks', 'status', 'clients']);

            return $this->sendResponse($project, 'Project updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error updating project: ' . $e->getMessage());
        }
    }

    /**
     * Delete a project
     */
    public function destroy($id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return $this->sendNotFound('Project not found');
            }

            // Check if project has tasks
            if ($project->tasks()->count() > 0) {
                return $this->sendError('Cannot delete project that has tasks');
            }

            $project->delete();

            return $this->sendResponse(null, 'Project deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting project: ' . $e->getMessage());
        }
    }

    /**
     * Get project statistics
     */
    public function getStatistics($id)
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return $this->sendNotFound('Project not found');
            }

            $statistics = [
                'total_tasks' => $project->tasks()->count(),
                'completed_tasks' => $project->tasks()->where('status_id', 3)->count(), // Assuming 3 is completed
                'pending_tasks' => $project->tasks()->where('status_id', '!=', 3)->count(),
                'total_users' => $project->users()->count(),
                'total_clients' => $project->clients()->count(),
                'progress_percentage' => $project->tasks()->count() > 0 
                    ? round(($project->tasks()->where('status_id', 3)->count() / $project->tasks()->count()) * 100, 2)
                    : 0,
            ];

            return $this->sendResponse($statistics, 'Project statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving project statistics: ' . $e->getMessage());
        }
    }
}

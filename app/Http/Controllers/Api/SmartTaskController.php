<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartTaskController extends Controller
{
    /**
     * Get HTTP client with API key authentication
     */
    private function getHttpClient()
    {
        return Http::withHeaders([
            'X-API-Key' => 'vck_IuYqGalsAzWt6TP8y2eg0ZhRj3sJNekU8lonoOtI'
        ]);
    }
    /**
     * Smart task management endpoint that executes actions determined by n8n AI
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function handleRequest(Request $request): JsonResponse
    {
        try {
            // Get the action and parameters from n8n AI
            $action = $request->input('action');
            $params = $request->input('params', []);
            $originalMessage = $request->input('message', '');
            
            Log::info('Smart Task Request', [
                'action' => $action,
                'params' => $params,
                'message' => $originalMessage
            ]);
            
            // Execute the action determined by n8n AI
            $result = $this->executeAction($action, $params, $originalMessage);
            
            return response()->json([
                'success' => true,
                'content' => $result['content'],
                'data' => $result['data'] ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Smart Task Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'content' => "❌ Sorry, I encountered an error: " . $e->getMessage(),
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Analyze the user request using OpenAI
     */
    private function analyzeRequest(string $message): array
    {
        // This would call OpenAI API to analyze the request
        // For now, let's implement basic pattern matching
        
        $lowerMessage = strtolower($message);
        
        // Detect intent patterns
        if (preg_match('/\b(kristine|john|sarah|admin)\b.*\b(tasks?|work|doing)\b/', $lowerMessage)) {
            return [
                'action' => 'get_user_tasks',
                'user_name' => $this->extractUserName($message),
                'confidence' => 0.9
            ];
        }
        
        if (preg_match('/\b(create|add|make|assign|delegate|ask|tell|get)\b.*\b(task|to|do|action)\b/', $lowerMessage)) {
            return [
                'action' => 'create_task',
                'title' => $this->extractTaskTitle($message),
                'assigned_to' => $this->extractUserName($message),
                'is_recurring' => $this->isRecurring($message),
                'recurring_pattern' => $this->extractRecurringPattern($message),
                'confidence' => 0.8
            ];
        }
        
        if (preg_match('/\b(what|show|list|get|find)\b.*\b(tasks?|work|doing|assigned)\b/', $lowerMessage)) {
            return [
                'action' => 'list_tasks',
                'filters' => $this->extractFilters($message),
                'confidence' => 0.7
            ];
        }
        
        if (preg_match('/\b(status|progress|done|completed|finished)\b/', $lowerMessage)) {
            return [
                'action' => 'get_task_status',
                'task_id' => $this->extractTaskId($message),
                'confidence' => 0.8
            ];
        }
        
        // Default to list all tasks
        return [
            'action' => 'list_tasks',
            'filters' => [],
            'confidence' => 0.5
        ];
    }
    
    /**
     * Execute the action determined by n8n AI
     */
    private function executeAction(string $action, array $params, string $originalMessage): array
    {
        switch ($action) {
            case 'get_user_tasks':
                return $this->getUserTasks($params);
                
            case 'create_task':
                return $this->createTask($params);
                
            case 'list_tasks':
                return $this->listTasks($params);
                
            case 'get_task_status':
                return $this->getTaskStatus($params);
                
            case 'get_users':
                return $this->getUsers($params);
                
            case 'get_projects':
                return $this->getProjects($params);
                
            case 'get_dashboard':
                return $this->getDashboard($params);
                
            case 'search_content':
                return $this->searchContent($params);
                
            case 'update_task':
                return $this->updateTask($params);
                
            case 'update_task_status':
                return $this->updateTaskStatus($params);
                
            case 'update_task_priority':
                return $this->updateTaskPriority($params);
                
            case 'delete_task':
                return $this->deleteTask($params);
                
            default:
                return [
                    'content' => "🤔 I'm not sure what you're asking for. Try asking me to:\n• Show tasks for a specific person\n• Create a new task\n• Check task status\n• List all tasks"
                ];
        }
    }
    
    /**
     * Get tasks for a specific user using existing API endpoints
     */
    private function getUserTasks(array $params): array
    {
        $userName = $params['user_name'] ?? null;
        
        if (!$userName) {
            return [
                'content' => "❌ Please specify a user name. Example: \"show me [name]'s tasks\""
            ];
        }
        
        try {
            // Use the existing search endpoint to find users
            $searchResponse = $this->getHttpClient()->get(secure_url('/api/search'), [
                'q' => $userName,
                'type' => 'users'
            ]);
            
            if (!$searchResponse->successful()) {
                return [
                    'content' => "❌ Unable to search for users. Please try again."
                ];
            }
            
            $searchData = $searchResponse->json();
            $users = $searchData['data']['users'] ?? [];
            
            if (empty($users)) {
                // Get all users to show available options
                $usersResponse = $this->getHttpClient()->get(secure_url('/api/users'));
                if ($usersResponse->successful()) {
                    $allUsers = $usersResponse->json()['data'] ?? [];
                    $userList = collect($allUsers)->map(function($u) {
                        $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        return "• {$displayName} ({$u['email']})";
                    })->join("\n");
                    
                    return [
                        'content' => "❌ User '{$userName}' not found.\n\n👥 Available users:\n{$userList}\n\nPlease check the spelling or use a different name."
                    ];
                }
                
                return [
                    'content' => "❌ User '{$userName}' not found. Please check the spelling and try again."
                ];
            }
            
            $user = $users[0]; // Take the first match
            
            // Use the existing tasks endpoint with user filter
            $tasksResponse = $this->getHttpClient()->get(secure_url('/api/tasks'), [
                'user_id' => $user['id']
            ]);
            
            if (!$tasksResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch tasks. Please try again."
                ];
            }
            
            $tasksData = $tasksResponse->json();
            $tasks = $tasksData['data'] ?? [];
            
            if (empty($tasks)) {
                $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                return [
                    'content' => "📋 {$displayName} has no tasks assigned.\n\n💡 You can create a task for them by saying: \"Create a task for {$displayName} to [action]\""
                ];
            }
            
            // Format tasks
            $taskList = collect($tasks)->map(function($task) {
                $status = $task['status']['name'] ?? 'Unknown';
                $priority = $task['priority']['name'] ?? 'Medium';
                $project = $task['project']['name'] ?? 'No Project';
                $dueDate = $task['end_date'] ? date('M j, Y', strtotime($task['end_date'])) : 'No due date';
                
                return "🟡 **{$task['title']}**\n   └ 📊 {$status} | 🎯 {$priority} | 📁 {$project} | 🗓️ {$dueDate}";
            })->join("\n\n");
            
            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            
            return [
                'content' => "📋 **{$displayName}'s Tasks** (" . count($tasks) . " total)\n\n{$taskList}\n\n💡 Need to create a task? Just ask: \"Create a task for {$displayName} to [action]\"",
                'data' => [
                    'user' => $user,
                    'tasks' => $tasks
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getUserTasks Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching user tasks. Please try again."
            ];
        }
    }
    
    /**
     * Create a new task using existing API endpoints
     */
    private function createTask(array $params): array
    {
        $title = $params['title'] ?? 'New Task';
        $description = $params['description'] ?? '';
        $assignedTo = $params['user_name'] ?? null;
        $isRecurring = $params['is_repeating'] ?? false;
        $repeatFrequency = $params['repeat_frequency'] ?? null;
        
        if (!$assignedTo) {
            return [
                'content' => "❌ Please specify who to assign the task to. Example: \"create task for [name] to [action]\""
            ];
        }
        
        try {
            // Use the existing search endpoint to find users
            $searchResponse = $this->getHttpClient()->get(secure_url('/api/search'), [
                'q' => $assignedTo,
                'type' => 'users'
            ]);
            
            if (!$searchResponse->successful()) {
                return [
                    'content' => "❌ Unable to search for users. Please try again."
                ];
            }
            
            $searchData = $searchResponse->json();
            $users = $searchData['data']['users'] ?? [];
            
            if (empty($users)) {
                // Get all users to show available options
                $usersResponse = $this->getHttpClient()->get(secure_url('/api/users'));
                if ($usersResponse->successful()) {
                    $allUsers = $usersResponse->json()['data'] ?? [];
                    $userList = collect($allUsers)->map(function($u) {
                        $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        return "• {$displayName} ({$u['email']})";
                    })->join("\n");
                    
                    return [
                        'content' => "❌ User '{$assignedTo}' not found.\n\n👥 Available users:\n{$userList}\n\nPlease check the spelling and try again."
                    ];
                }
                
                return [
                    'content' => "❌ User '{$assignedTo}' not found. Please check the spelling and try again."
                ];
            }
            
            $user = $users[0]; // Take the first match
            
            // Use the existing tasks endpoint to create a task
            $taskData = [
                'title' => $title,
                'description' => "Task created via Smart API: {$title}",
                'status_id' => 20, // Active
                'priority_id' => 2, // Medium
                'project_id' => 19, // Default project
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addDays(7)->format('Y-m-d'),
                'is_repeating' => $isRecurring,
                'repeat_frequency' => $isRecurring ? 'weekly' : null,
                'repeat_interval' => $isRecurring ? 1 : null,
                'user_ids' => [$user['id']] // Assign to user
            ];
            
            $taskResponse = $this->getHttpClient()->post(secure_url('/api/tasks'), $taskData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to create task. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $recurringText = $isRecurring ? " (recurring weekly)" : "";
            
            return [
                'content' => "✅ **Task Created Successfully!**\n\n🟡 **{$task['title']}**\n   └ 👤 Assigned to: {$displayName}\n   └ 📊 Status: Active\n   └ 🎯 Priority: Medium\n   └ 🗓️ Due: " . now()->addDays(7)->format('M j, Y') . "{$recurringText}\n\n💡 You can check on this task anytime by asking: \"What tasks does {$displayName} have?\"",
                'data' => [
                    'task' => $task,
                    'user' => $user
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task createTask Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while creating the task. Please try again."
            ];
        }
    }
    
    /**
     * List tasks with filters using existing API endpoints
     */
    private function listTasks(array $analysis): array
    {
        $filters = $analysis['filters'] ?? [];
        
        try {
            // Build query parameters for the existing tasks endpoint
            $queryParams = [];
            
            if (isset($filters['status'])) {
                $queryParams['status_id'] = $filters['status'];
            }
            
            if (isset($filters['user_id'])) {
                $queryParams['user_id'] = $filters['user_id'];
            }
            
            // Use the existing tasks endpoint
            $tasksResponse = $this->getHttpClient()->get(secure_url('/api/tasks'), $queryParams);
            
            if (!$tasksResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch tasks. Please try again."
                ];
            }
            
            $tasksData = $tasksResponse->json();
            $tasks = $tasksData['data'] ?? [];
            
            if (empty($tasks)) {
                return [
                    'content' => "📋 No tasks found matching your criteria.\n\n💡 Try asking:\n• \"What tasks are active?\"\n• \"Show me all tasks\"\n• \"What tasks does [name] have?\""
                ];
            }
            
            // Format tasks
            $taskList = collect($tasks)->map(function($task) {
                $assignees = collect($task['users'] ?? [])->map(function($u) {
                    return trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                })->join(', ');
                $status = $task['status']['name'] ?? 'Unknown';
                $priority = $task['priority']['name'] ?? 'Medium';
                $project = $task['project']['name'] ?? 'No Project';
                $dueDate = $task['end_date'] ? date('M j, Y', strtotime($task['end_date'])) : 'No due date';
                
                return "🟡 **{$task['title']}**\n   └ 👤 {$assignees} | 📊 {$status} | 🎯 {$priority} | 📁 {$project} | 🗓️ {$dueDate}";
            })->join("\n\n");
            
            return [
                'content' => "📋 **Tasks Found** (" . count($tasks) . " total)\n\n{$taskList}\n\n💡 Need to create a task? Just ask: \"Create a task for [name] to [action]\"",
                'data' => $tasks
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task listTasks Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching tasks. Please try again."
            ];
        }
    }
    
    /**
     * Get task status using existing API endpoints
     */
    private function getTaskStatus(array $analysis): array
    {
        $taskId = $analysis['task_id'];
        
        if (!$taskId) {
            return [
                'content' => "❌ Please specify a task ID. Example: \"Check status of task #123\""
            ];
        }
        
        try {
            // Use the existing tasks endpoint to get a specific task
            $taskResponse = $this->getHttpClient()->get(secure_url("/api/tasks/{$taskId}"));
            
            if (!$taskResponse->successful()) {
                if ($taskResponse->status() === 404) {
                    return [
                        'content' => "❌ Task #{$taskId} not found. Please check the task ID and try again."
                    ];
                }
                return [
                    'content' => "❌ Unable to fetch task. Please try again."
                ];
            }
            
            $taskData = $taskResponse->json();
            $task = $taskData['data'] ?? [];
            
            if (empty($task)) {
                return [
                    'content' => "❌ Task #{$taskId} not found. Please check the task ID and try again."
                ];
            }
            
            $assignees = collect($task['users'] ?? [])->map(function($u) {
                return trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
            })->join(', ');
            $status = $task['status']['name'] ?? 'Unknown';
            $priority = $task['priority']['name'] ?? 'Medium';
            $project = $task['project']['name'] ?? 'No Project';
            $dueDate = $task['end_date'] ? date('M j, Y', strtotime($task['end_date'])) : 'No due date';
            
            return [
                'content' => "📊 **Task #{$task['id']} Status**\n\n🟡 **{$task['title']}**\n   └ 👤 Assigned to: {$assignees}\n   └ 📊 Status: {$status}\n   └ 🎯 Priority: {$priority}\n   └ 📁 Project: {$project}\n   └ 🗓️ Due: {$dueDate}\n\n💡 Need to update this task? Just ask: \"Mark task #{$task['id']} as completed\"",
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getTaskStatus Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching task status. Please try again."
            ];
        }
    }
    
    /**
     * Get users using existing API endpoints
     */
    private function getUsers(array $params): array
    {
        try {
            $usersResponse = $this->getHttpClient()->get(secure_url('/api/users'));
            
            if (!$usersResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch users. Please try again."
                ];
            }
            
            $usersData = $usersResponse->json();
            $users = $usersData['data'] ?? [];
            
            if (empty($users)) {
                return [
                    'content' => "👥 No users found."
                ];
            }
            
            $userList = collect($users)->map(function($u) {
                $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                return "• {$displayName} ({$u['email']})";
            })->join("\n");
            
            return [
                'content' => "👥 **Available Users** (" . count($users) . " total)\n\n{$userList}",
                'data' => $users
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getUsers Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching users. Please try again."
            ];
        }
    }
    
    /**
     * Get projects using existing API endpoints
     */
    private function getProjects(array $params): array
    {
        try {
            $projectsResponse = $this->getHttpClient()->get(secure_url('/api/projects'));
            
            if (!$projectsResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch projects. Please try again."
                ];
            }
            
            $projectsData = $projectsResponse->json();
            $projects = $projectsData['data'] ?? [];
            
            if (empty($projects)) {
                return [
                    'content' => "📁 No projects found."
                ];
            }
            
            $projectList = collect($projects)->map(function($p) {
                return "• {$p['name']} (ID: {$p['id']})";
            })->join("\n");
            
            return [
                'content' => "📁 **Available Projects** (" . count($projects) . " total)\n\n{$projectList}",
                'data' => $projects
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getProjects Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching projects. Please try again."
            ];
        }
    }
    
    /**
     * Get dashboard data using existing API endpoints
     */
    private function getDashboard(array $params): array
    {
        try {
            $dashboardResponse = $this->getHttpClient()->get(secure_url('/api/dashboard'));
            
            if (!$dashboardResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch dashboard data. Please try again."
                ];
            }
            
            $dashboardData = $dashboardResponse->json();
            $data = $dashboardData['data'] ?? [];
            
            return [
                'content' => "📊 **Dashboard Overview**\n\n• Total Tasks: " . ($data['total_tasks'] ?? 'N/A') . "\n• Active Tasks: " . ($data['active_tasks'] ?? 'N/A') . "\n• Completed Tasks: " . ($data['completed_tasks'] ?? 'N/A'),
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getDashboard Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching dashboard data. Please try again."
            ];
        }
    }
    
    /**
     * Search content using existing API endpoints
     */
    private function searchContent(array $params): array
    {
        $query = $params['query'] ?? '';
        
        if (!$query) {
            return [
                'content' => "❌ Please provide a search query. Example: \"search for [term]\""
            ];
        }
        
        try {
            $searchResponse = $this->getHttpClient()->get(secure_url('/api/search'), [
                'q' => $query
            ]);
            
            if (!$searchResponse->successful()) {
                return [
                    'content' => "❌ Unable to search. Please try again."
                ];
            }
            
            $searchData = $searchResponse->json();
            $results = $searchData['data'] ?? [];
            
            $content = "🔍 **Search Results for '{$query}'**\n\n";
            
            if (!empty($results['tasks'])) {
                $content .= "📋 **Tasks** (" . count($results['tasks']) . ")\n";
                foreach (array_slice($results['tasks'], 0, 5) as $task) {
                    $content .= "• {$task['title']}\n";
                }
                $content .= "\n";
            }
            
            if (!empty($results['users'])) {
                $content .= "👥 **Users** (" . count($results['users']) . ")\n";
                foreach (array_slice($results['users'], 0, 5) as $user) {
                    $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    $content .= "• {$displayName} ({$user['email']})\n";
                }
                $content .= "\n";
            }
            
            if (!empty($results['projects'])) {
                $content .= "📁 **Projects** (" . count($results['projects']) . ")\n";
                foreach (array_slice($results['projects'], 0, 5) as $project) {
                    $content .= "• {$project['name']}\n";
                }
            }
            
            if (empty($results['tasks']) && empty($results['users']) && empty($results['projects'])) {
                $content = "🔍 **No results found for '{$query}'**\n\nTry different search terms or check spelling.";
            }
            
            return [
                'content' => $content,
                'data' => $results
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task searchContent Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while searching. Please try again."
            ];
        }
    }
    
    /**
     * Update task using existing API endpoints
     */
    private function updateTask(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        $updateData = $params['update_data'] ?? [];
        
        if (!$taskId) {
            return [
                'content' => "❌ Please specify a task ID to update."
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/tasks/{$taskId}"), $updateData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to update task. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            
            return [
                'content' => "✅ **Task #{$taskId} Updated Successfully!**\n\n🟡 **{$task['title']}**",
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task updateTask Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while updating the task. Please try again."
            ];
        }
    }
    
    /**
     * Update task status using existing API endpoints
     */
    private function updateTaskStatus(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        $statusId = $params['status_id'] ?? null;
        
        if (!$taskId || !$statusId) {
            return [
                'content' => "❌ Please specify both task ID and status ID."
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/tasks/{$taskId}"), [
                'status_id' => $statusId
            ]);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to update task status. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            
            return [
                'content' => "✅ **Task #{$taskId} Status Updated!**\n\n🟡 **{$task['title']}**\n   └ 📊 Status: " . ($task['status']['name'] ?? 'Unknown'),
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task updateTaskStatus Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while updating task status. Please try again."
            ];
        }
    }
    
    /**
     * Update task priority using existing API endpoints
     */
    private function updateTaskPriority(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        $priorityId = $params['priority_id'] ?? null;
        
        if (!$taskId || !$priorityId) {
            return [
                'content' => "❌ Please specify both task ID and priority ID."
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/tasks/{$taskId}"), [
                'priority_id' => $priorityId
            ]);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to update task priority. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            
            return [
                'content' => "✅ **Task #{$taskId} Priority Updated!**\n\n🟡 **{$task['title']}**\n   └ 🎯 Priority: " . ($task['priority']['name'] ?? 'Unknown'),
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task updateTaskPriority Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while updating task priority. Please try again."
            ];
        }
    }
    
    /**
     * Delete task using existing API endpoints
     */
    private function deleteTask(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        
        if (!$taskId) {
            return [
                'content' => "❌ Please specify a task ID to delete."
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->delete(secure_url("/api/tasks/{$taskId}"));
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to delete task. Please try again."
                ];
            }
            
            return [
                'content' => "✅ **Task #{$taskId} Deleted Successfully!**"
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task deleteTask Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while deleting the task. Please try again."
            ];
        }
    }
    
    // Helper methods
    private function extractUserName(string $message): ?string
    {
        if (preg_match('/\b(kristine|john|sarah|admin)\b/i', $message, $matches)) {
            return strtolower($matches[1]);
        }
        return null;
    }
    
    private function extractTaskTitle(string $message): string
    {
        // Simple extraction - in real implementation, use OpenAI
        if (preg_match('/\b(create|add|make|assign|delegate|ask|tell|get)\b.*?\b(to|do)\b\s+(.+)/i', $message, $matches)) {
            return trim($matches[3]);
        }
        return 'New Task';
    }
    
    private function isRecurring(string $message): bool
    {
        return preg_match('/\b(every|once|weekly|daily|monthly|monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i', $message);
    }
    
    private function extractRecurringPattern(string $message): ?string
    {
        if (preg_match('/\b(every|once|weekly|daily|monthly|monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i', $message, $matches)) {
            return strtolower($matches[1]);
        }
        return null;
    }
    
    private function extractFilters(string $message): array
    {
        $filters = [];
        
        // Status filters
        if (preg_match('/\b(active|in progress|completed|pending|submitted|archive)\b/i', $message, $matches)) {
            $statusMap = [
                'active' => 20,
                'in progress' => 16,
                'completed' => 17,
                'pending' => 15,
                'submitted' => 23,
                'archive' => 24
            ];
            $filters['status'] = $statusMap[strtolower($matches[1])] ?? null;
        }
        
        return $filters;
    }
    
    private function extractTaskId(string $message): ?int
    {
        if (preg_match('/#(\d+)/', $message, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI\Client;

class SmartTaskController extends Controller
{
    /**
     * Get HTTP client with API key authentication
     */
    private function getHttpClient()
    {
        return Http::withHeaders([
            'X-API-Key' => config('app.smart_api_key', env('SMART_API_KEY'))
        ]);
    }
    
    /**
     * Log HTTP request details for debugging
     */
    private function logHttpRequest($method, $url, $data = null, $response = null)
    {
        Log::info('Smart API HTTP Request', [
            'method' => $method,
            'url' => $url,
            'data' => $data,
            'response_status' => $response ? $response->status() : null,
            'response_body' => $response ? $response->body() : null,
            'response_headers' => $response ? $response->headers() : null,
            'successful' => $response ? $response->successful() : null
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
                'message' => $originalMessage,
                'all_input' => $request->all()
            ]);
            
            // If no action provided or action is unknown, use OpenAI fallback
            if (!$action || $action === 'unknown' || $action === 'fallback') {
                Log::info('Smart API using OpenAI fallback', [
                    'reason' => 'No action or unknown action',
                    'action' => $action,
                    'message' => $originalMessage
                ]);
                
                $result = $this->openAIFallback($originalMessage, $params);
            } else {
                // Execute the action determined by n8n AI
                $result = $this->executeAction($action, $params, $originalMessage);
            }
            
            Log::info('Smart Task Response', [
                'action' => $action,
                'result' => $result
            ]);
            
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
            
            // If there's an error and we have a message, try OpenAI fallback as last resort
            $originalMessage = $request->input('message', '');
            if ($originalMessage) {
                try {
                    Log::info('Smart API trying OpenAI fallback after error', [
                        'error' => $e->getMessage(),
                        'message' => $originalMessage
                    ]);
                    
                    $fallbackResult = $this->openAIFallback($originalMessage, $request->input('params', []));
                    
                    return response()->json([
                        'success' => true,
                        'content' => $fallbackResult['content'],
                        'data' => $fallbackResult['data'] ?? null
                    ]);
                } catch (\Exception $fallbackError) {
                    Log::error('Smart API OpenAI fallback also failed', [
                        'original_error' => $e->getMessage(),
                        'fallback_error' => $fallbackError->getMessage()
                    ]);
                }
            }
            
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
        Log::info('Smart API executeAction', [
            'action' => $action,
            'params' => $params,
            'originalMessage' => $originalMessage
        ]);
        
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
                
            case 'get_project_progress':
                return $this->getProjectProgress($params);
                
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
            // Get all users and search locally for better matching
            $usersResponse = $this->getHttpClient()->get(secure_url('/api/v1/users'));
            
            if (!$usersResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch users. Please try again."
                ];
            }
            
            $usersData = $usersResponse->json();
            $allUsers = $usersData['data'] ?? [];
            
            // Search for user by name (case-insensitive partial match)
            $foundUser = null;
            $userNameLower = strtolower($userName);
            $candidates = [];
            
            foreach ($allUsers as $user) {
                $fullName = strtolower($user['first_name'] . ' ' . $user['last_name']);
                $firstName = strtolower($user['first_name']);
                $lastName = strtolower($user['last_name']);
                
                $score = 0;
                
                // Exact first name match gets highest priority
                if ($firstName === $userNameLower) {
                    $score = 100;
                    // Bonus points for admin role when there's an exact first name match
                    if (isset($user['roles']) && is_array($user['roles'])) {
                        foreach ($user['roles'] as $role) {
                            if (isset($role['name']) && strtolower($role['name']) === 'admin') {
                                $score += 10; // Admin bonus
                                break;
                            }
                        }
                    }
                }
                // Exact full name match gets high priority
                elseif ($fullName === $userNameLower) {
                    $score = 90;
                }
                // First name starts with search term
                elseif (strpos($firstName, $userNameLower) === 0) {
                    $score = 80;
                }
                // Full name starts with search term
                elseif (strpos($fullName, $userNameLower) === 0) {
                    $score = 70;
                }
                // First name contains search term
                elseif (strpos($firstName, $userNameLower) !== false) {
                    $score = 60;
                }
                // Full name contains search term
                elseif (strpos($fullName, $userNameLower) !== false) {
                    $score = 50;
                }
                // Last name contains search term
                elseif (strpos($lastName, $userNameLower) !== false) {
                    $score = 40;
                }
                
                if ($score > 0) {
                    $candidates[] = ['user' => $user, 'score' => $score];
                }
            }
            
            // Sort candidates by score (highest first) and take the best match
            if (!empty($candidates)) {
                usort($candidates, function($a, $b) {
                    return $b['score'] - $a['score'];
                });
                $foundUser = $candidates[0]['user'];
            }
            
            if (!$foundUser) {
                $userList = collect($allUsers)->map(function($u) {
                    $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                    return "• {$displayName} ({$u['email']})";
            })->join("\n");
            
            return [
                'content' => "❌ User '{$userName}' not found.\n\n👥 Available users:\n{$userList}\n\nPlease check the spelling or use a different name."
            ];
        }
        
            $user = $foundUser;
            
            // Use the existing tasks endpoint with user filter
            $tasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
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
        $priority = $params['priority'] ?? null;
        $dueDate = $params['due_date'] ?? null;
        $projectId = $params['project_id'] ?? null;
        
        Log::info('Smart API createTask - Starting task creation', [
            'title' => $title,
            'assigned_to' => $assignedTo,
            'params' => $params
        ]);
        
        if (!$assignedTo) {
            return [
                'content' => "❌ Please specify who to assign the task to. Example: \"create task for [name] to [action]\""
            ];
        }
        
        // First, check if a task with this title already exists
        try {
            // Search for tasks with the same title (get more results to catch all matches)
            $existingTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                'search' => $title,
                'per_page' => 50
            ]);
            
            if ($existingTasksResponse->successful()) {
                $existingTasks = $existingTasksResponse->json()['data'] ?? [];
                
                // Look for exact title match
                foreach ($existingTasks as $task) {
                    if (strtolower($task['title']) === strtolower($title)) {
                        // Found existing task, reassign it instead of creating new one
                        Log::info('Smart API createTask - Found existing task, reassigning', [
                            'existing_task_id' => $task['id'],
                            'title' => $title,
                            'assigned_to' => $assignedTo
                        ]);
                        return $this->reassignTask($task['id'], $assignedTo, $title);
                    }
                }
            }
            
            // If search didn't find it, try getting all tasks and search locally
            $allTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                'per_page' => 100
            ]);
            
            if ($allTasksResponse->successful()) {
                $allTasks = $allTasksResponse->json()['data'] ?? [];
                
                // Look for exact title match in all tasks
                foreach ($allTasks as $task) {
                    if (strtolower($task['title']) === strtolower($title)) {
                        // Found existing task, reassign it instead of creating new one
                        Log::info('Smart API createTask - Found existing task in all tasks, reassigning', [
                            'existing_task_id' => $task['id'],
                            'title' => $title,
                            'assigned_to' => $assignedTo
                        ]);
                        return $this->reassignTask($task['id'], $assignedTo, $title);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Smart API createTask - Error checking existing tasks', ['error' => $e->getMessage()]);
            // Continue with normal task creation if search fails
        }
        
        try {
            // Get all users and search locally for better matching
            $usersResponse = $this->getHttpClient()->get(secure_url('/api/v1/users'));
            
            if (!$usersResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch users. Please try again."
                ];
            }
            
            $usersData = $usersResponse->json();
            $allUsers = $usersData['data'] ?? [];
            
            // Search for user by name (case-insensitive partial match)
            $foundUser = null;
            $userNameLower = strtolower($assignedTo);
            $candidates = [];
            
            foreach ($allUsers as $user) {
                $fullName = strtolower($user['first_name'] . ' ' . $user['last_name']);
                $firstName = strtolower($user['first_name']);
                $lastName = strtolower($user['last_name']);
                
                $score = 0;
                
                // Exact first name match gets highest priority
                if ($firstName === $userNameLower) {
                    $score = 100;
                    // Bonus points for admin role when there's an exact first name match
                    if (isset($user['roles']) && is_array($user['roles'])) {
                        foreach ($user['roles'] as $role) {
                            if (isset($role['name']) && strtolower($role['name']) === 'admin') {
                                $score += 10; // Admin bonus
                                break;
                            }
                        }
                    }
                }
                // Exact full name match gets high priority
                elseif ($fullName === $userNameLower) {
                    $score = 90;
                }
                // First name starts with search term
                elseif (strpos($firstName, $userNameLower) === 0) {
                    $score = 80;
                }
                // Full name starts with search term
                elseif (strpos($fullName, $userNameLower) === 0) {
                    $score = 70;
                }
                // First name contains search term
                elseif (strpos($firstName, $userNameLower) !== false) {
                    $score = 60;
                }
                // Full name contains search term
                elseif (strpos($fullName, $userNameLower) !== false) {
                    $score = 50;
                }
                // Last name contains search term
                elseif (strpos($lastName, $userNameLower) !== false) {
                    $score = 40;
                }
                
                if ($score > 0) {
                    $candidates[] = ['user' => $user, 'score' => $score];
                }
            }
            
            // Sort candidates by score (highest first) and take the best match
            if (!empty($candidates)) {
                usort($candidates, function($a, $b) {
                    return $b['score'] - $a['score'];
                });
                $foundUser = $candidates[0]['user'];
            }
            
            if (!$foundUser) {
                $userList = collect($allUsers)->map(function($u) {
                    $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                    return "• {$displayName} ({$u['email']})";
                })->join("\n");
            
            return [
                'content' => "❌ User '{$assignedTo}' not found.\n\n👥 Available users:\n{$userList}\n\nPlease check the spelling and try again."
            ];
        }
        
            $user = $foundUser;
            
            // Use the existing tasks endpoint to create a task
            $taskData = [
            'title' => $title,
                'description' => $description ?: "Task created via Smart API: {$title}",
            'status_id' => 20, // Active
            'priority_id' => $this->getPriorityId($priority) ?? 2, // Default to Medium
            'project_id' => $projectId ?? 19, // Default project
                'start_date' => now()->format('Y-m-d'),
                'end_date' => $dueDate ? \Carbon\Carbon::parse($dueDate)->format('Y-m-d') : now()->addDays(7)->format('Y-m-d'),
            'is_repeating' => $isRecurring,
            'repeat_frequency' => $isRecurring ? 'weekly' : null,
            'repeat_interval' => $isRecurring ? 1 : null,
                'user_ids' => [$user['id']] // Assign to user
            ];
            
            $taskResponse = $this->getHttpClient()->post(secure_url('/api/v1/tasks'), $taskData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to create task. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $recurringText = $isRecurring ? " (recurring weekly)" : "";
        
        return [
                'content' => "✅ **Task Created Successfully!**\n\n🟡 **{$task['title']}**\n   └ 👤 Assigned to: {$displayName}\n   └ 📊 Status: Active\n   └ 🎯 Priority: " . ($task['priority']['title'] ?? 'Medium') . "\n   └ 🗓️ Due: " . (isset($task['end_date']) ? \Carbon\Carbon::parse($task['end_date'])->format('M j, Y') : now()->addDays(7)->format('M j, Y')) . "{$recurringText}\n\n💡 You can check on this task anytime by asking: \"What tasks does {$displayName} have?\"",
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
     * Get priority ID from priority name
     */
    private function getPriorityId(?string $priority): ?int
    {
        if (!$priority) {
            return null;
        }
        
        $priorityMap = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'urgent' => 4,
        ];
        
        return $priorityMap[strtolower($priority)] ?? null;
    }
    
    /**
     * Get status ID from status name
     */
    private function getStatusId(?string $status): ?int
    {
        if (!$status) {
            return null;
        }
        
        $statusMap = [
            'active' => 20,
            'completed' => 17,
            'pending' => 21,
            'in_progress' => 22,
            'cancelled' => 23,
            'archive' => 24,
        ];
        
        return $statusMap[strtolower($status)] ?? null;
    }
    
    /**
     * Reassign an existing task to a new user
     */
    private function reassignTask(int $taskId, string $assignedTo, string $taskTitle): array
    {
        try {
            // Get all users and search for the target user
            $usersResponse = $this->getHttpClient()->get(secure_url('/api/v1/users'));
            
            if (!$usersResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch users. Please try again."
                ];
            }
            
            $usersData = $usersResponse->json();
            $allUsers = $usersData['data'] ?? [];
            
            // Search for user by name (case-insensitive partial match)
            $foundUser = null;
            $userNameLower = strtolower($assignedTo);
            $candidates = [];
            
            foreach ($allUsers as $user) {
                $fullName = strtolower($user['first_name'] . ' ' . $user['last_name']);
                $firstName = strtolower($user['first_name']);
                $lastName = strtolower($user['last_name']);
                
                $score = 0;
                
                // Exact first name match gets highest priority
                if ($firstName === $userNameLower) {
                    $score = 100;
                    // Bonus points for admin role when there's an exact first name match
                    if (isset($user['roles']) && is_array($user['roles'])) {
                        foreach ($user['roles'] as $role) {
                            if (isset($role['name']) && strtolower($role['name']) === 'admin') {
                                $score += 10; // Admin bonus
                                break;
                            }
                        }
                    }
                }
                // Exact full name match gets high priority
                elseif ($fullName === $userNameLower) {
                    $score = 90;
                }
                // First name starts with search term
                elseif (strpos($firstName, $userNameLower) === 0) {
                    $score = 80;
                }
                // Full name starts with search term
                elseif (strpos($fullName, $userNameLower) === 0) {
                    $score = 70;
                }
                // First name contains search term
                elseif (strpos($firstName, $userNameLower) !== false) {
                    $score = 60;
                }
                // Full name contains search term
                elseif (strpos($fullName, $userNameLower) !== false) {
                    $score = 50;
                }
                // Last name contains search term
                elseif (strpos($lastName, $userNameLower) !== false) {
                    $score = 40;
                }
                
                if ($score > 0) {
                    $candidates[] = ['user' => $user, 'score' => $score];
                }
            }
            
            // Sort candidates by score (highest first) and take the best match
            if (!empty($candidates)) {
                usort($candidates, function($a, $b) {
                    return $b['score'] - $a['score'];
                });
                $foundUser = $candidates[0]['user'];
            }
            
            if (!$foundUser) {
                $userList = collect($allUsers)->map(function($u) {
                    $displayName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                    return "• {$displayName} ({$u['email']})";
                })->join("\n");
                
                return [
                    'content' => "❌ User '{$assignedTo}' not found.\n\n👥 Available users:\n{$userList}\n\nPlease check the spelling or use a different name."
                ];
            }
            
            // Update the task to assign it to the new user
            $updateData = [
                'user_ids' => [$foundUser['id']]
            ];
            
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/v1/tasks/{$taskId}"), $updateData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to reassign task. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            $displayName = trim(($foundUser['first_name'] ?? '') . ' ' . ($foundUser['last_name'] ?? ''));
            
            return [
                'content' => "✅ **Task Reassigned Successfully!**\n\n🟡 **{$taskTitle}**\n   └ 👤 Reassigned to: {$displayName}\n   └ 📊 Status: " . ($task['status']['title'] ?? 'Active') . "\n   └ 🎯 Priority: " . ($task['priority']['title'] ?? 'Medium') . "\n   └ 🗓️ Due: " . (isset($task['end_date']) ? \Carbon\Carbon::parse($task['end_date'])->format('M j, Y') : 'Not set') . "\n\n💡 You can check on this task anytime by asking: \"What tasks does {$displayName} have?\"",
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task reassignTask Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while reassigning the task. Please try again."
            ];
        }
    }
    
    /**
     * List tasks with filters using existing API endpoints
     */
    private function listTasks(array $analysis): array
    {
        $filters = $analysis['filters'] ?? [];
        
        Log::info('Smart API listTasks called', [
            'filters' => $filters,
            'analysis' => $analysis
        ]);
        
        try {
            // Build query parameters for the existing tasks endpoint
            $queryParams = [];
            
        if (isset($filters['status'])) {
                $queryParams['status_id'] = $filters['status'];
        }
        
        if (isset($filters['user_id'])) {
                $queryParams['user_id'] = $filters['user_id'];
            }
            
            $url = secure_url('/api/v1/tasks');
            Log::info('Smart API making tasks request', [
                'url' => $url,
                'queryParams' => $queryParams
            ]);
            
            // Use the existing tasks endpoint
            $tasksResponse = $this->getHttpClient()->get($url, $queryParams);
            
            // Log the response details
            $this->logHttpRequest('GET', $url, $queryParams, $tasksResponse);
            
            if (!$tasksResponse->successful()) {
                Log::error('Smart API tasks request failed', [
                    'status' => $tasksResponse->status(),
                    'body' => $tasksResponse->body(),
                    'headers' => $tasksResponse->headers()
                ]);
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
            $taskResponse = $this->getHttpClient()->get(secure_url("/api/v1/tasks/{$taskId}"));
            
            if (!$taskResponse->successful()) {
                if ($taskResponse->status() === 404) {
                    return [
                        'content' => "❌ Task not found. Please check the task details and try again."
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
                    'content' => "❌ Task not found. Please check the task details and try again."
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
                'content' => "📊 **Task Status**\n\n🟡 **{$task['title']}**\n   └ 👤 Assigned to: {$assignees}\n   └ 📊 Status: {$status}\n   └ 🎯 Priority: {$priority}\n   └ 📁 Project: {$project}\n   └ 🗓️ Due: {$dueDate}\n\n💡 Need to update this task? Just ask: \"Mark this task as completed\"",
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
            $usersResponse = $this->getHttpClient()->get(secure_url('/api/v1/users'));
            
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
            $projectsResponse = $this->getHttpClient()->get(secure_url('/api/v1/projects'));
            
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
                return "• {$p['title']}";
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
     * Get project progress using existing API endpoints
     */
    private function getProjectProgress(array $params): array
    {
        $projectName = $params['project_name'] ?? null;
        
        if (!$projectName) {
            return [
                'content' => "❌ Please specify a project name. Example: \"what's the progress of [project name]\""
            ];
        }
        
        try {
            // Get all projects and search for the specified project
            $projectsResponse = $this->getHttpClient()->get(secure_url('/api/v1/projects'));
            
            if (!$projectsResponse->successful()) {
                return [
                    'content' => "❌ Unable to fetch projects. Please try again."
                ];
            }
            
            $projectsData = $projectsResponse->json();
            $allProjects = $projectsData['data'] ?? [];
            
            // Search for project by name (case-insensitive partial match)
            $foundProject = null;
            $projectNameLower = strtolower($projectName);
            
            foreach ($allProjects as $project) {
                $title = strtolower($project['title']);
                
                if (strpos($title, $projectNameLower) !== false) {
                    $foundProject = $project;
                    break;
                }
            }
            
            if (!$foundProject) {
                $projectList = collect($allProjects)->map(function($p) {
                    return "• {$p['title']}";
                })->join("\n");
                
                return [
                    'content' => "❌ Project '{$projectName}' not found.\n\n📁 Available projects:\n{$projectList}\n\nPlease check the spelling or use a different name."
                ];
            }
            
            // Format project progress information
            $totalTasks = $foundProject['total_tasks'] ?? 0;
            $completedTasks = $foundProject['completed_tasks'] ?? 0;
            $activeTasks = $foundProject['active_tasks'] ?? 0;
            $overdueTasks = $foundProject['overdue_tasks'] ?? 0;
            $status = $foundProject['status']['title'] ?? 'Unknown';
            
            $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            
            $progressBar = str_repeat('🟩', min(10, $progressPercentage / 10)) . str_repeat('⬜', max(0, 10 - ($progressPercentage / 10)));
            
            $content = "📊 **Project Progress: {$foundProject['title']}**\n\n";
            $content .= "📈 **Overall Progress: {$progressPercentage}%**\n";
            $content .= "{$progressBar}\n\n";
            $content .= "📋 **Task Breakdown:**\n";
            $content .= "   • Total Tasks: {$totalTasks}\n";
            $content .= "   • Completed: {$completedTasks}\n";
            $content .= "   • Active: {$activeTasks}\n";
            $content .= "   • Overdue: {$overdueTasks}\n\n";
            $content .= "📊 **Status:** {$status}\n";
            
            if ($foundProject['description']) {
                $content .= "\n📝 **Description:** {$foundProject['description']}\n";
            }
            
            return [
                'content' => $content,
                'data' => $foundProject
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getProjectProgress Error', ['error' => $e->getMessage()]);
            return [
                'content' => "❌ Sorry, I encountered an error while fetching project progress. Please try again."
            ];
        }
    }
    
    /**
     * Get dashboard data using existing API endpoints
     */
    private function getDashboard(array $params): array
    {
        try {
            $dashboardResponse = $this->getHttpClient()->get(secure_url('/api/v1/dashboard'));
            
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
            $searchResponse = $this->getHttpClient()->get(secure_url('/api/v1/search'), [
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
        $taskTitle = $params['title'] ?? null;
        $updateData = $params['update_data'] ?? [];
        
        // Handle direct field updates (like due_date, priority, etc.)
        if (isset($params['due_date'])) {
            $updateData['end_date'] = \Carbon\Carbon::parse($params['due_date'])->format('Y-m-d');
        }
        if (isset($params['priority'])) {
            $updateData['priority_id'] = $this->getPriorityId($params['priority']);
        }
        if (isset($params['status'])) {
            $updateData['status_id'] = $this->getStatusId($params['status']);
        }
        
        // If no task ID provided, search for task by title
        if (!$taskId && $taskTitle) {
            try {
                // Search for tasks with the same title
                $existingTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                    'search' => $taskTitle,
                    'per_page' => 50
                ]);
                
                if ($existingTasksResponse->successful()) {
                    $existingTasks = $existingTasksResponse->json()['data'] ?? [];
                    
                    // Look for exact title match
                    foreach ($existingTasks as $task) {
                        if (strtolower($task['title']) === strtolower($taskTitle)) {
                            $taskId = $task['id'];
                            break;
                        }
                    }
                }
                
                // If search didn't find it, try getting all tasks and search locally
                if (!$taskId) {
                    $allTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                        'per_page' => 100
                    ]);
                    
                    if ($allTasksResponse->successful()) {
                        $allTasks = $allTasksResponse->json()['data'] ?? [];
                        
                        // Look for exact title match in all tasks
                        foreach ($allTasks as $task) {
                            if (strtolower($task['title']) === strtolower($taskTitle)) {
                                $taskId = $task['id'];
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Smart API updateTask - Error searching for task', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$taskId) {
            return [
                'content' => "❌ Task not found. Please check the task title or provide a task ID."
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/v1/tasks/{$taskId}"), $updateData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to update task. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            
            return [
                'content' => "✅ **Task Updated Successfully!**\n\n🟡 **{$task['title']}**",
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
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/v1/tasks/{$taskId}"), [
                'status_id' => $statusId
            ]);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to update task status. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            
            return [
                'content' => "✅ **Task Status Updated!**\n\n🟡 **{$task['title']}**\n   └ 📊 Status: " . ($task['status']['title'] ?? 'Unknown'),
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
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/v1/tasks/{$taskId}"), [
                'priority_id' => $priorityId
            ]);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to update task priority. Please try again."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
        
        return [
                'content' => "✅ **Task Priority Updated!**\n\n🟡 **{$task['title']}**\n   └ 🎯 Priority: " . ($task['priority']['title'] ?? 'Unknown'),
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
        $taskTitle = $params['title'] ?? null;
        
        // If no task ID provided, search for task by title
        if (!$taskId && $taskTitle) {
            try {
                // Search for tasks with the same title
                $existingTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                    'search' => $taskTitle,
                    'per_page' => 50
                ]);
                
                if ($existingTasksResponse->successful()) {
                    $existingTasks = $existingTasksResponse->json()['data'] ?? [];
                    
                    // Look for exact title match
                    foreach ($existingTasks as $task) {
                        if (strtolower($task['title']) === strtolower($taskTitle)) {
                            $taskId = $task['id'];
                            break;
                        }
                    }
                }
                
                // If search didn't find it, try getting all tasks and search locally
                if (!$taskId) {
                    $allTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                        'per_page' => 100
                    ]);
                    
                    if ($allTasksResponse->successful()) {
                        $allTasks = $allTasksResponse->json()['data'] ?? [];
                        
                        // Look for exact title match in all tasks
                        foreach ($allTasks as $task) {
                            if (strtolower($task['title']) === strtolower($taskTitle)) {
                                $taskId = $task['id'];
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Smart API deleteTask - Error searching for task', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$taskId) {
            return [
                'content' => "❌ Task not found. Please check the task title or provide a task ID."
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->delete(secure_url("/api/v1/tasks/{$taskId}"));
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => "❌ Unable to delete task. Please try again."
                ];
            }
            
            return [
                'content' => "✅ **Task Deleted Successfully!**"
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
    
    /**
     * OpenAI fallback for complex requests
     */
    private function openAIFallback(string $message, array $params = []): array
    {
        try {
            $apiKey = config('app.openai_api_key');
            if (!$apiKey) {
                return [
                    'content' => "❌ OpenAI API key not configured. Please contact your administrator."
                ];
            }
            
            // Create the system prompt with API documentation
            $systemPrompt = $this->getSystemPrompt();
            
            // Create the user prompt
            $userPrompt = "User request: \"{$message}\"\n\nParameters: " . json_encode($params, JSON_PRETTY_PRINT);
            
            Log::info('Smart API OpenAI Fallback', [
                'message' => $message,
                'params' => $params
            ]);
            
            $client = new Client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.3
            ]);
            
            $aiResponse = $response->choices[0]->message->content;
            
            Log::info('Smart API OpenAI Response', [
                'response' => $aiResponse
            ]);
            
            // Try to parse the AI response as JSON
            $parsedResponse = json_decode($aiResponse, true);
            
            if ($parsedResponse && isset($parsedResponse['action'])) {
                // AI provided a structured response, execute it
                return $this->executeAction($parsedResponse['action'], $parsedResponse['params'] ?? [], $message);
            } else {
                // AI provided a natural language response
                return [
                    'content' => $aiResponse,
                    'data' => ['ai_fallback' => true]
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Smart API OpenAI Fallback Error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            
            return [
                'content' => "❌ I'm having trouble understanding your request. Please try rephrasing it or contact support if the issue persists."
            ];
        }
    }
    
    /**
     * Get the system prompt for OpenAI
     */
    private function getSystemPrompt(): string
    {
        return "You are an intelligent task management assistant for VendorConnect. You help users manage tasks, projects, and team members through natural language commands.

AVAILABLE ACTIONS:
1. create_task - Create a new task
   Parameters: title, description, user_name, priority (low/medium/high/urgent), due_date, project_id
   
2. update_task - Update an existing task
   Parameters: title (to find task), due_date, priority, status, description
   
3. delete_task - Delete a task
   Parameters: title (to find task) or task_id
   
4. get_user_tasks - Get tasks for a specific user
   Parameters: user_name
   
5. list_tasks - List tasks with filters
   Parameters: status, priority, user_name, project_name
   
6. get_task_status - Get status of a specific task
   Parameters: title (to find task) or task_id
   
7. get_projects - List all projects
   Parameters: none
   
8. get_project_progress - Get progress of a specific project
   Parameters: project_name
   
9. get_users - List all users
   Parameters: none
   
10. get_dashboard - Get dashboard overview
    Parameters: none

RESPONSE FORMAT:
If you can determine the correct action and parameters, respond with JSON:
{
  \"action\": \"action_name\",
  \"params\": {
    \"param1\": \"value1\",
    \"param2\": \"value2\"
  }
}

If you cannot determine the action or need clarification, respond with natural language explaining what you understand and what additional information you need.

EXAMPLES:
- \"create a task for John to review the proposal\" → {\"action\": \"create_task\", \"params\": {\"title\": \"Review the proposal\", \"user_name\": \"John\"}}
- \"what tasks does Sarah have?\" → {\"action\": \"get_user_tasks\", \"params\": {\"user_name\": \"Sarah\"}}
- \"delete the marketing task\" → {\"action\": \"delete_task\", \"params\": {\"title\": \"marketing task\"}}
- \"change the due date for project review to next Friday\" → {\"action\": \"update_task\", \"params\": {\"title\": \"project review\", \"due_date\": \"next Friday\"}}

Always be helpful and provide clear, actionable responses.";
    }
}

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
            $searchResponse = Http::get(url('/api/search'), [
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
                $usersResponse = Http::get(url('/api/users'));
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
            $tasksResponse = Http::get(url('/api/tasks'), [
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
            $searchResponse = Http::get(url('/api/search'), [
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
                $usersResponse = Http::get(url('/api/users'));
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
            
            $taskResponse = Http::post(url('/api/tasks'), $taskData);
            
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
            $tasksResponse = Http::get(url('/api/tasks'), $queryParams);
            
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
            $taskResponse = Http::get(url("/api/tasks/{$taskId}"));
            
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

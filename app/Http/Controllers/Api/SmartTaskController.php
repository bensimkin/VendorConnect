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
            // Set a reasonable execution time limit for Smart API operations
            set_time_limit(60); // 60 seconds max
            
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
                'content' => $this->generateConversationalResponse('generic_error') . "\n\n**Technical details:** " . $e->getMessage(),
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
                
            case 'get_task_updates':
                return $this->getTaskUpdates($params);
                
            case 'add_task_message':
                return $this->addTaskMessage($params);
                
            case 'add_task_attachment':
                return $this->addTaskAttachment($params);
                
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
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the user list right now."
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
                'content' => "👤 I don't see anyone named \"{$userName}\" in our team. Let me show you who's available:\n\n👥 **Available users:**\n{$userList}\n\n💡 **Tips for finding users:**\n• Try just the first name (e.g., \"John\" instead of \"John Smith\")\n• Check the spelling\n• Ask me to list all users to see everyone available"
            ];
        }
        
            $user = $foundUser;
            
            // Use the existing tasks endpoint with user filter
            $tasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                'user_id' => $user['id']
            ]);
            
            if (!$tasksResponse->successful()) {
            return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the task list right now."
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
                $status = $task['status']['title'] ?? 'Unknown';
                $priority = $task['priority']['title'] ?? 'Medium';
                $project = $task['project']['title'] ?? 'No Project';
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI ran into trouble getting the user's tasks."
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
        $isRecurring = $params['is_recurring'] ?? $params['is_repeating'] ?? false;
        $recurringPattern = $params['recurring_pattern'] ?? null;
        $repeatFrequency = $params['repeat_frequency'] ?? null;
        $priority = $params['priority'] ?? null;
        $dueDate = $params['due_date'] ?? null;
        $projectId = $params['project_id'] ?? null;
        $templateId = $params['template_id'] ?? null;
        $templateName = $params['template_name'] ?? null;
        
        Log::info('Smart API createTask - Starting task creation', [
            'title' => $title,
            'assigned_to' => $assignedTo,
            'template_id' => $templateId,
            'template_name' => $templateName,
            'params' => $params
        ]);
        
        if (!$assignedTo) {
            return [
                'content' => "❌ Please specify who to assign the task to. Example: \"create task for [name] to [action]\""
            ];
        }
        
        // First, check if a task with this title already exists (optimized single call)
        try {
            // Single optimized search with larger per_page to avoid second call
            $existingTasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                'search' => $title,
                'per_page' => 200  // Increased to catch more results
            ]);
            
            if ($existingTasksResponse->successful()) {
                $existingTasks = $existingTasksResponse->json()['data'] ?? [];
                
                // Look for exact title match first
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
                
                // If no exact match, try fuzzy matching
                $bestMatch = $this->findBestTaskMatch($title, $existingTasks);
                if ($bestMatch) {
                    // Check if this is a disambiguation response
                    if (isset($bestMatch['content']) && isset($bestMatch['data']['disambiguation'])) {
                        return $bestMatch; // Return the disambiguation response
                    }
                    
                    Log::info('Smart API createTask - Found fuzzy match, reassigning', [
                        'existing_task_id' => $bestMatch['id'],
                        'existing_title' => $bestMatch['title'],
                        'requested_title' => $title,
                        'assigned_to' => $assignedTo
                    ]);
                    return $this->reassignTask($bestMatch['id'], $assignedTo, $title);
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
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the user list right now."
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
                'content' => "👤 I don't see anyone named \"{$assignedTo}\" in our team. Let me show you who's available:\n\n👥 **Available users:**\n{$userList}\n\n💡 **Tips for finding users:**\n• Try just the first name (e.g., \"John\" instead of \"John Smith\")\n• Check the spelling\n• Ask me to list all users to see everyone available"
            ];
        }
        
            $user = $foundUser;
            
            // Calculate start date for recurring tasks
            $startDate = now()->format('Y-m-d');
            if ($isRecurring && $recurringPattern) {
                $startDate = $this->calculateRecurringStartDate($recurringPattern);
            }
            
            // Use the existing tasks endpoint to create a task
            $taskData = [
            'title' => $title,
                'description' => $description ?: "Task created via Smart API: {$title}",
            'status_id' => 20, // Active
            'priority_id' => $this->getPriorityId($priority) ?? 2, // Default to Medium
            'project_id' => $projectId ?? 19, // Default project
                'start_date' => $startDate,
                'end_date' => $dueDate ? \Carbon\Carbon::parse($dueDate)->format('Y-m-d') : \Carbon\Carbon::parse($startDate)->addDays(7)->format('Y-m-d'),
            'is_repeating' => $isRecurring,
            'repeat_frequency' => $isRecurring ? 'weekly' : null,
            'repeat_interval' => $isRecurring ? 1 : null,
                'user_ids' => [$user['id']] // Assign to user
            ];
            
            // Add template support - both ID and name
            if ($templateId) {
                $taskData['template_id'] = $templateId;
                Log::info('Smart API createTask - Using template ID', ['template_id' => $templateId]);
            } elseif ($templateName) {
                $taskData['template_name'] = $templateName;
                Log::info('Smart API createTask - Using template name', ['template_name' => $templateName]);
            }
            
            $taskResponse = $this->getHttpClient()->post(secure_url('/api/v1/tasks'), $taskData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI couldn't create that task for you right now."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            
            // Add attachment if provided
            $attachmentUrl = $params['attachment_url'] ?? $params['url'] ?? $params['link'] ?? $params['google_drive_url'] ?? null;
            if ($attachmentUrl && filter_var($attachmentUrl, FILTER_VALIDATE_URL)) {
                $attachmentDescription = $params['attachment_description'] ?? 'Task attachment';
                $messageText = "📎 **Task Attachment**\n\n**Description:** {$attachmentDescription}\n\n**Link:** {$attachmentUrl}";
                
                // Add attachment as a message
                $this->getHttpClient()->post(secure_url("/api/v1/tasks/{$task['id']}/messages"), [
                    'message' => $messageText,
                    'user_id' => 1 // Default to super admin
                ]);
            }
            
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble creating that task for you."
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
     * Check if a string looks like a date
     */
    private function isDateString(string $str): bool
    {
        $str = trim($str);
        
        // Check for common date patterns
        $datePatterns = [
            '/^\d{4}-\d{2}-\d{2}$/', // YYYY-MM-DD
            '/^\d{2}\/\d{2}\/\d{4}$/', // MM/DD/YYYY
            '/^\d{2}-\d{2}-\d{4}$/', // MM-DD-YYYY
            '/^[a-zA-Z]+ \d{1,2}$/', // October 22
            '/^[a-zA-Z]+ \d{1,2}, \d{4}$/', // October 22, 2025
            '/^\d{1,2} [a-zA-Z]+$/', // 22 October
            '/^\d{1,2} [a-zA-Z]+ \d{4}$/', // 22 October 2025
        ];
        
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $str)) {
                return true;
            }
        }
        
        // Try to parse with Carbon
        try {
            \Carbon\Carbon::parse($str);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Find the best matching task using fuzzy matching
     */
    private function findBestTaskMatch(string $searchTitle, array $tasks): ?array
    {
        $searchTitle = strtolower(trim($searchTitle));
        $matches = [];
        
        foreach ($tasks as $task) {
            $taskTitle = strtolower(trim($task['title']));
            $score = $this->calculateTaskMatchScore($searchTitle, $taskTitle);
            
            if ($score >= 0.6) { // Minimum 60% match
                $matches[] = [
                    'task' => $task,
                    'score' => $score
                ];
            }
        }
        
        if (empty($matches)) {
            return null;
        }
        
        // Sort by score (highest first)
        usort($matches, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // If only one match, return it
        if (count($matches) === 1) {
            return $matches[0]['task'];
        }
        
        // If multiple matches with similar high scores, ask for disambiguation
        $topMatches = array_filter($matches, function($match) use ($matches) {
            return $match['score'] >= $matches[0]['score'] - 0.1; // Within 10% of best score
        });
        
        if (count($topMatches) > 1) {
            return $this->createDisambiguationResponse($searchTitle, $topMatches);
        }
        
        // Return the best match
        return $matches[0]['task'];
    }
    
    /**
     * Calculate similarity score between two task titles
     */
    private function calculateTaskMatchScore(string $searchTitle, string $taskTitle): float
    {
        // Remove common words that don't affect matching
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $searchWords = array_diff(explode(' ', $searchTitle), $commonWords);
        $taskWords = array_diff(explode(' ', $taskTitle), $commonWords);
        
        if (empty($searchWords) || empty($taskWords)) {
            return 0;
        }
        
        $matches = 0;
        $totalWords = count($searchWords);
        
        foreach ($searchWords as $searchWord) {
            foreach ($taskWords as $taskWord) {
                // Exact word match
                if ($searchWord === $taskWord) {
                    $matches += 1;
                    break;
                }
                // Partial word match (for plurals, etc.)
                if (strpos($taskWord, $searchWord) !== false || strpos($searchWord, $taskWord) !== false) {
                    $matches += 0.7;
                    break;
                }
            }
        }
        
        return $matches / $totalWords;
    }
    
    /**
     * Generate conversational and helpful error responses
     */
    private function generateConversationalResponse(string $type, array $context = []): string
    {
        $responses = [
            'no_search_results' => [
                "🤔 Hmm, I couldn't find anything matching \"" . ($context['query'] ?? 'your search') . "\". Let me help you out!",
                "🔍 I searched high and low, but \"" . ($context['query'] ?? 'your search') . "\" didn't match anything in our system.",
                "😅 Oops! Nothing came up for \"" . ($context['query'] ?? 'your search') . "\". Maybe we can try something else?",
                "🤷‍♂️ I'm drawing a blank on \"" . ($context['query'] ?? 'your search') . "\". Let's try a different approach!"
            ],
            'user_not_found' => [
                "👤 I don't see anyone named \"" . ($context['user'] ?? 'that person') . "\" in our team. Let me show you who's available:",
                "🤔 Hmm, \"" . ($context['user'] ?? 'that person') . "\" doesn't ring a bell. Here are the people I know:",
                "😅 I can't find \"" . ($context['user'] ?? 'that person') . "\" in our user list. Maybe check out these folks:",
                "👥 \"" . ($context['user'] ?? 'that person') . "\" isn't in our system, but here's who I can find:"
            ],
            'task_not_found' => [
                "📋 I don't see a task called \"" . ($context['task'] ?? 'that task') . "\" anywhere. Let me help you find what you're looking for!",
                "🤔 Hmm, \"" . ($context['task'] ?? 'that task') . "\" doesn't match any of our current tasks. Want to see what's available?",
                "😅 I'm not finding \"" . ($context['task'] ?? 'that task') . "\" in our task list. Maybe we can try something else?",
                "📝 \"" . ($context['task'] ?? 'that task') . "\" isn't showing up in our tasks. Let me show you what we have:"
            ],
            'project_not_found' => [
                "📁 I don't see a project called \"" . ($context['project'] ?? 'that project') . "\" in our system. Here's what I can find:",
                "🤔 \"" . ($context['project'] ?? 'that project') . "\" doesn't match any of our projects. Maybe check out these:",
                "😅 I can't locate \"" . ($context['project'] ?? 'that project') . "\" in our project list. Here are the ones I know:",
                "📂 \"" . ($context['project'] ?? 'that project') . "\" isn't in our projects. Want to see what's available?"
            ],
            'api_error' => [
                "😬 Oops! Something went wrong on my end. Let me try to fix that for you.",
                "🤖 I hit a little snag there. Give me a moment to sort this out.",
                "😅 Well, that didn't go as planned. Let me try a different approach.",
                "🔄 I'm having a bit of trouble with that request. Let me try again."
            ],
            'generic_error' => [
                "😬 Something unexpected happened. Let me help you get back on track.",
                "🤖 I encountered an issue, but don't worry - I'm here to help!",
                "😅 Oops! That didn't work as expected. Let's try something else.",
                "🔄 I hit a bump in the road. Let me find a better way to help you."
            ]
        ];
        
        $typeResponses = $responses[$type] ?? $responses['generic_error'];
        $baseResponse = $typeResponses[array_rand($typeResponses)];
        
        // Add helpful suggestions based on context
        $suggestions = [];
        
        switch ($type) {
            case 'no_search_results':
                $suggestions = [
                    "💡 **Try these instead:**",
                    "• Use simpler keywords (e.g., \"tasks\" instead of \"quarterly report tasks\")",
                    "• Check your spelling",
                    "• Ask me to list all tasks, users, or projects to see what's available",
                    "• Try searching for just part of what you're looking for"
                ];
                break;
                
            case 'user_not_found':
                $suggestions = [
                    "💡 **Tips for finding users:**",
                    "• Try just the first name (e.g., \"John\" instead of \"John Smith\")",
                    "• Check the spelling",
                    "• Ask me to list all users to see everyone available"
                ];
                break;
                
            case 'task_not_found':
                $suggestions = [
                    "💡 **To find tasks, try:**",
                    "• Ask me to list all tasks",
                    "• Search for part of the task name",
                    "• Check the spelling",
                    "• Ask \"What tasks does [user] have?\" to see user-specific tasks"
                ];
                break;
                
            case 'project_not_found':
                $suggestions = [
                    "💡 **To find projects, try:**",
                    "• Ask me to list all projects",
                    "• Use simpler project names",
                    "• Check the spelling",
                    "• Ask \"What projects are active?\" to see current projects"
                ];
                break;
                
            case 'api_error':
            case 'generic_error':
                $suggestions = [
                    "💡 **What you can do:**",
                    "• Try rephrasing your request",
                    "• Ask me to list available options",
                    "• Try a simpler version of what you're looking for",
                    "• Let me know if this keeps happening!"
                ];
                break;
        }
        
        return $baseResponse . "\n\n" . implode("\n", $suggestions);
    }
    
    /**
     * Create a disambiguation response when multiple tasks match
     */
    private function createDisambiguationResponse(string $searchTitle, array $matches): array
    {
        $response = "🤔 **Multiple tasks found for \"{$searchTitle}\"**\n\n";
        $response .= "I found " . count($matches) . " similar tasks. Which one did you mean?\n\n";
        
        foreach ($matches as $index => $match) {
            $task = $match['task'];
            $score = round($match['score'] * 100);
            
            // Get task details
            $assignedTo = 'Unassigned';
            if (!empty($task['users']) && is_array($task['users'])) {
                $userNames = array_map(function($user) {
                    return $user['first_name'] . ' ' . $user['last_name'];
                }, $task['users']);
                $assignedTo = implode(', ', $userNames);
            }
            
            $status = $task['status']['title'] ?? 'Unknown';
            $priority = $task['priority']['title'] ?? 'Unknown';
            $dueDate = 'No due date';
            if (!empty($task['end_date'])) {
                $dueDate = \Carbon\Carbon::parse($task['end_date'])->format('M j, Y');
            }
            
            $response .= "**" . ($index + 1) . ".** 🟡 **{$task['title']}** ({$score}% match)\n";
            $response .= "   └ 👤 Assigned to: {$assignedTo}\n";
            $response .= "   └ 📊 Status: {$status}\n";
            $response .= "   └ 🎯 Priority: {$priority}\n";
            $response .= "   └ 🗓️ Due: {$dueDate}\n\n";
        }
        
        $response .= "💡 **To specify which task, try:**\n";
        $response .= "• \"Update the first one\" or \"Update the second one\"\n";
        $response .= "• \"Update [exact task title]\"\n";
        $response .= "• \"Update the one assigned to [user name]\"\n";
        $response .= "• \"Update the one due [date]\"\n\n";
        $response .= "Or be more specific with the task name to avoid confusion.";
        
        return [
            'content' => $response,
            'data' => [
                'disambiguation' => true,
                'matches' => $matches,
                'search_title' => $searchTitle
            ]
        ];
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
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the user list right now."
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
                'content' => "👤 I don't see anyone named \"{$assignedTo}\" in our team. Let me show you who's available:\n\n👥 **Available users:**\n{$userList}\n\n💡 **Tips for finding users:**\n• Try just the first name (e.g., \"John\" instead of \"John Smith\")\n• Check the spelling\n• Ask me to list all users to see everyone available"
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble reassigning that task for you."
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
            // Use dashboard endpoint to get all tasks across all projects
            $dashboardResponse = $this->getHttpClient()->get(secure_url('/api/v1/dashboard'));
            
            if (!$dashboardResponse->successful()) {
                Log::error('Smart API dashboard request failed', [
                    'status' => $dashboardResponse->status(),
                    'body' => $dashboardResponse->body()
                ]);
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the task list right now."
                ];
            }
            
            $dashboardData = $dashboardResponse->json();
            $data = $dashboardData['data'] ?? [];
            $tasks = $data['recent_tasks'] ?? [];
            
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
                $status = $task['status']['title'] ?? 'Unknown';
                $priority = $task['priority']['title'] ?? 'Medium';
                $project = $task['project']['title'] ?? 'No Project';
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the task list."
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
            $status = $task['status']['title'] ?? 'Unknown';
            $priority = $task['priority']['title'] ?? 'Medium';
            $project = $task['project']['title'] ?? 'No Project';
            $dueDate = $task['end_date'] ? date('M j, Y', strtotime($task['end_date'])) : 'No due date';
            
            return [
                'content' => "📊 **Task Status**\n\n🟡 **{$task['title']}**\n   └ 👤 Assigned to: {$assignees}\n   └ 📊 Status: {$status}\n   └ 🎯 Priority: {$priority}\n   └ 📁 Project: {$project}\n   └ 🗓️ Due: {$dueDate}\n\n💡 Need to update this task? Just ask: \"Mark this task as completed\"",
            'data' => $task
        ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getTaskStatus Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the task status."
            ];
        }
    }
    
    /**
     * Get task updates/messages using existing API endpoints
     */
    private function getTaskUpdates(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        $taskTitle = $params['task_title'] ?? null;
        
        // If no task ID provided, try to find task by title
        if (!$taskId && $taskTitle) {
            try {
                $tasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                    'search' => $taskTitle,
                    'per_page' => 200
                ]);
                
                if ($tasksResponse->successful()) {
                    $tasks = $tasksResponse->json()['data'] ?? [];
                    foreach ($tasks as $task) {
                        if (strtolower($task['title']) === strtolower($taskTitle)) {
                            $taskId = $task['id'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Smart Task getTaskUpdates - Error finding task by title', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$taskId) {
            return [
                'content' => "❌ **Task Not Found**\n\nI couldn't find a task with the specified title or ID.\n\n💡 **Suggestions:**\n• Check the spelling of the task name\n• Try a partial match (e.g., \"GHL\" instead of \"Update GHL Sales Data\")\n• Ask me to list all tasks to see what's available"
            ];
        }
        
        try {
            // Get task messages
            $messagesResponse = $this->getHttpClient()->get(secure_url("/api/v1/tasks/{$taskId}/messages"));
            
            if (!$messagesResponse->successful()) {
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the task updates right now."
                ];
            }
            
            $messagesData = $messagesResponse->json();
            $messages = $messagesData['data'] ?? [];
            
            if (empty($messages)) {
                return [
                    'content' => "📝 **No Updates Found**\n\nThis task doesn't have any messages or updates yet.\n\n💡 **To add an update:**\n• Ask me to add a message to this task\n• Or update the task status/description"
                ];
            }
            
            // Format messages
            $updatesList = collect($messages)->map(function($message) {
                $senderName = trim(($message['sender']['first_name'] ?? '') . ' ' . ($message['sender']['last_name'] ?? ''));
                $sentAt = \Carbon\Carbon::parse($message['sent_at'])->format('M j, Y \a\t g:i A');
                
                return "💬 **{$senderName}** ({$sentAt})\n   └ {$message['message_text']}";
            })->join("\n\n");
            
            return [
                'content' => "📝 **Task Updates** (" . count($messages) . " total)\n\n{$updatesList}\n\n💡 **To add an update:**\n• Ask me to add a message to this task",
                'data' => $messages
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getTaskUpdates Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the task updates."
            ];
        }
    }
    
    /**
     * Add a message/update to a task using existing API endpoints
     */
    private function addTaskMessage(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        $taskTitle = $params['task_title'] ?? null;
        $message = $params['message'] ?? $params['update'] ?? null;
        $userId = $params['user_id'] ?? 1; // Default to super admin if not specified
        
        if (!$message) {
            return [
                'content' => "❌ **No Message Provided**\n\nPlease provide a message to add to the task.\n\n💡 **Example:**\n• \"Add message: Working on this task, will complete by Friday\"\n• \"Ask user: What's the current status of this task?\""
            ];
        }
        
        // If no task ID provided, try to find task by title
        if (!$taskId && $taskTitle) {
            try {
                $tasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                    'search' => $taskTitle,
                    'per_page' => 200
                ]);
                
                if ($tasksResponse->successful()) {
                    $tasks = $tasksResponse->json()['data'] ?? [];
                    foreach ($tasks as $task) {
                        if (strtolower($task['title']) === strtolower($taskTitle)) {
                            $taskId = $task['id'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Smart Task addTaskMessage - Error finding task by title', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$taskId) {
            return [
                'content' => "❌ **Task Not Found**\n\nI couldn't find a task with the specified title or ID.\n\n💡 **Suggestions:**\n• Check the spelling of the task name\n• Try a partial match (e.g., \"GHL\" instead of \"Update GHL Sales Data\")\n• Ask me to list all tasks to see what's available"
            ];
        }
        
        try {
            // Add message to task
            $messageResponse = $this->getHttpClient()->post(secure_url("/api/v1/tasks/{$taskId}/messages"), [
                'message' => $message,
                'user_id' => $userId
            ]);
            
            if (!$messageResponse->successful()) {
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble adding the message to the task right now."
                ];
            }
            
            $messageData = $messageResponse->json();
            $addedMessage = $messageData['data'] ?? [];
            
            // Get user info for the response
            $userName = 'Unknown User';
            if (isset($addedMessage['sender'])) {
                $sender = $addedMessage['sender'];
                $userName = trim(($sender['first_name'] ?? '') . ' ' . ($sender['last_name'] ?? ''));
            }
            
            $sentAt = \Carbon\Carbon::parse($addedMessage['created_at'])->format('M j, Y \a\t g:i A');
            
            return [
                'content' => "✅ **Message Added Successfully!**\n\n💬 **{$userName}** ({$sentAt})\n   └ {$message}\n\n📝 **Task:** {$taskTitle}\n\n💡 **To view all updates:**\n• Ask me to show updates for this task",
                'data' => $addedMessage
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task addTaskMessage Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble adding the message to the task."
            ];
        }
    }
    
    /**
     * Add file attachment or Google Drive link to a task using existing API endpoints
     */
    private function addTaskAttachment(array $params): array
    {
        $taskId = $params['task_id'] ?? null;
        $taskTitle = $params['task_title'] ?? null;
        $attachmentUrl = $params['url'] ?? $params['link'] ?? $params['google_drive_url'] ?? null;
        $description = $params['description'] ?? null;
        
        // If no task ID provided, try to find task by title
        if (!$taskId && $taskTitle) {
            try {
                $tasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                    'search' => $taskTitle,
                    'per_page' => 200
                ]);
                
                if ($tasksResponse->successful()) {
                    $tasks = $tasksResponse->json()['data'] ?? [];
                    foreach ($tasks as $task) {
                        if (strtolower($task['title']) === strtolower($taskTitle)) {
                            $taskId = $task['id'];
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Smart Task addTaskAttachment - Error finding task by title', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$taskId) {
            return [
                'content' => "❌ **Task Not Found**\n\nI couldn't find a task with the specified title or ID.\n\n💡 **Suggestions:**\n• Check the spelling of the task name\n• Try a partial match (e.g., \"GHL\" instead of \"Update GHL Sales Data\")\n• Ask me to list all tasks to see what's available"
            ];
        }
        
        if (!$attachmentUrl) {
            return [
                'content' => "❌ **No Attachment Provided**\n\nPlease provide a file URL or Google Drive link to attach to the task.\n\n💡 **Examples:**\n• \"Add Google Drive link: https://drive.google.com/file/d/...\"\n• \"Attach file: https://example.com/document.pdf\"\n• \"Add link: https://docs.google.com/spreadsheets/d/...\""
            ];
        }
        
        try {
            // Validate URL format
            if (!filter_var($attachmentUrl, FILTER_VALIDATE_URL)) {
                return [
                    'content' => "❌ **Invalid URL**\n\nThe provided URL is not valid. Please provide a proper URL.\n\n💡 **Examples:**\n• Google Drive: https://drive.google.com/file/d/...\n• Google Docs: https://docs.google.com/document/d/...\n• Google Sheets: https://docs.google.com/spreadsheets/d/..."
                ];
            }
            
            // Add attachment as a message with the URL
            $messageText = "📎 **Attachment Added**\n\n";
            if ($description) {
                $messageText .= "**Description:** {$description}\n\n";
            }
            $messageText .= "**Link:** {$attachmentUrl}";
            
            // Add the attachment as a message
            $messageResponse = $this->getHttpClient()->post(secure_url("/api/v1/tasks/{$taskId}/messages"), [
                'message' => $messageText,
                'user_id' => 1 // Default to super admin
            ]);
            
            if (!$messageResponse->successful()) {
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble adding the attachment to the task right now."
                ];
            }
            
            $messageData = $messageResponse->json();
            $addedMessage = $messageData['data'] ?? [];
            
            // Determine attachment type
            $attachmentType = 'Link';
            if (strpos($attachmentUrl, 'drive.google.com') !== false) {
                $attachmentType = 'Google Drive';
            } elseif (strpos($attachmentUrl, 'docs.google.com') !== false) {
                $attachmentType = 'Google Docs';
            } elseif (strpos($attachmentUrl, 'sheets.google.com') !== false) {
                $attachmentType = 'Google Sheets';
            } elseif (preg_match('/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|png|jpg|jpeg|gif)$/i', $attachmentUrl)) {
                $attachmentType = 'File';
            }
            
            return [
                'content' => "✅ **Attachment Added Successfully!**\n\n📎 **{$attachmentType}**\n   └ {$attachmentUrl}\n\n📝 **Task:** {$taskTitle}\n\n💡 **To view all attachments:**\n• Ask me to show updates for this task",
                'data' => $addedMessage
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task addTaskAttachment Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble adding the attachment to the task."
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
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the user list right now."
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the user list."
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
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the project list right now."
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the project list."
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
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI'm having trouble accessing the project list right now."
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
                'content' => "📁 I don't see a project called \"{$projectName}\" in our system. Here's what I can find:\n\n📁 **Available projects:**\n{$projectList}\n\n💡 **To find projects, try:**\n• Ask me to list all projects\n• Use simpler project names\n• Check the spelling\n• Ask \"What projects are active?\" to see current projects"
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the project progress."
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
            
            // Extract task statistics from the correct data structure
            $overview = $data['overview'] ?? [];
            $taskStats = $data['task_statistics'] ?? [];
            $statusStats = $taskStats['by_status'] ?? [];
            
            // Calculate active and completed tasks from status statistics
            $activeTasks = 0;
            $completedTasks = 0;
            
            foreach ($statusStats as $statusId => $count) {
                // Status ID 20 = Active, Status ID 17 = Completed
                if ($statusId == 20) {
                    $activeTasks = $count;
                } elseif ($statusId == 17) {
                    $completedTasks = $count;
                }
            }
            
            $totalTasks = $overview['total_tasks'] ?? array_sum($statusStats);
            
            return [
                'content' => "📊 **Dashboard Overview**\n\n• Total Tasks: {$totalTasks}\n• Active Tasks: {$activeTasks}\n• Completed Tasks: {$completedTasks}",
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task getDashboard Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble getting the dashboard data."
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
                    'content' => "😬 Oops! Something went wrong on my end. Let me try to fix that for you.\n\nI'm having trouble searching right now.\n\n💡 **What you can do:**\n• Try rephrasing your request\n• Ask me to list available options\n• Try a simpler version of what you're looking for\n• Let me know if this keeps happening!"
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
                    $content .= "• {$project['title']}\n";
                }
            }
            
            if (empty($results['tasks']) && empty($results['users']) && empty($results['projects'])) {
                // Check if this looks like a project status/progress query
                $queryLower = strtolower($query);
                $projectKeywords = ['status of project', 'progress of project', 'project status', 'project progress', 'how is project', 'project update', 'how is', 'going', 'how are', 'how\'s'];
                
                $isProjectQuery = false;
                foreach ($projectKeywords as $keyword) {
                    if (strpos($queryLower, $keyword) !== false) {
                        $isProjectQuery = true;
                        break;
                    }
                }
                
                if ($isProjectQuery) {
                    // Extract potential project name from the query
                    $projectName = $this->extractProjectNameFromQuery($query);
                    
                    if ($projectName) {
                        $content = "🤔 I couldn't find \"{$query}\" in the search results, but it looks like you're asking about project status!\n\n";
                        $content .= "💡 **Try asking:** \"What's the progress of {$projectName}?\" or \"Show me the status of {$projectName} project\"\n\n";
                        $content .= "🔧 **Available Actions:**\n";
                        $content .= "• Ask for project progress: \"progress of [project name]\"\n";
                        $content .= "• List all projects: \"show me all projects\"\n";
                        $content .= "• Get dashboard overview: \"show me the dashboard\"";
                    } else {
                        $content = "🤔 I couldn't find anything matching \"{$query}\", but it looks like you're asking about project status!\n\n";
                        $content .= "💡 **Try these instead:**\n";
                        $content .= "• \"What's the progress of [project name]?\"\n";
                        $content .= "• \"Show me the status of [project name] project\"\n";
                        $content .= "• \"List all projects\" to see available projects\n";
                        $content .= "• \"Show me the dashboard\" for overall status";
                    }
                } else {
                    $content = "🤔 Hmm, I couldn't find anything matching \"{$query}\". Let me help you out!\n\n💡 **Try these instead:**\n• Use simpler keywords (e.g., \"tasks\" instead of \"quarterly report tasks\")\n• Check your spelling\n• Ask me to list all tasks, users, or projects to see what's available\n• Try searching for just part of what you're looking for";
                }
            }
            
            return [
                'content' => $content,
                'data' => $results
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task searchContent Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble searching for that."
            ];
        }
    }
    
    /**
     * Extract project name from a query string
     */
    private function extractProjectNameFromQuery(string $query): ?string
    {
        $queryLower = strtolower($query);
        
        // Common patterns for project queries
        $patterns = [
            '/status of project (.+)/i',
            '/progress of project (.+)/i',
            '/project status (.+)/i',
            '/project progress (.+)/i',
            '/how is project (.+)/i',
            '/project update (.+)/i',
            '/status of (.+) project/i',
            '/progress of (.+) project/i',
            '/how is (.+) going/i',
            '/how are (.+) going/i',
            '/how\'s (.+) going/i',
            '/how is (.+) project/i',
            '/how are (.+) project/i',
            '/how\'s (.+) project/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                $projectName = trim($matches[1]);
                // Remove common words that might be at the end
                $projectName = preg_replace('/\s+(project|status|progress|update)$/i', '', $projectName);
                return $projectName;
            }
        }
        
        return null;
    }
    
    /**
     * Update task using existing API endpoints
     */
    private function updateTask(array $params): array
    {
        // Handle different parameter formats from n8n
        $taskId = null;
        $taskTitle = null;
        $updateData = $params['update_data'] ?? [];
        
        // Determine if task_id is a numeric ID or a title string
        if (isset($params['task_id'])) {
            if (is_numeric($params['task_id'])) {
                $taskId = (int) $params['task_id'];
            } else {
                $taskTitle = $params['task_id']; // Treat as title
            }
        }
        
        // Use title parameter if provided
        if (isset($params['title'])) {
            $taskTitle = $params['title'];
        }
        
        // Handle direct field updates (like due_date, priority, etc.)
        if (isset($params['due_date'])) {
            $updateData['end_date'] = \Carbon\Carbon::parse($params['due_date'])->format('Y-m-d');
        } elseif (isset($params['description']) && $this->isDateString($params['description'])) {
            // n8n sometimes sends date in description field
            $updateData['end_date'] = \Carbon\Carbon::parse($params['description'])->format('Y-m-d');
        }
        if (isset($params['priority'])) {
            $updateData['priority_id'] = $this->getPriorityId($params['priority']);
        }
        if (isset($params['status'])) {
            $updateData['status_id'] = $this->getStatusId($params['status']);
        }
        
        // If no task ID provided, search for task by title (optimized single call)
        if (!$taskId && $taskTitle) {
            try {
                // Single optimized search with larger per_page to avoid second call
                $tasksResponse = $this->getHttpClient()->get(secure_url('/api/v1/tasks'), [
                    'search' => $taskTitle,
                    'per_page' => 200  // Increased to catch more results
                ]);
                
                if ($tasksResponse->successful()) {
                    $tasks = $tasksResponse->json()['data'] ?? [];
                    
                    // Look for exact title match first
                    foreach ($tasks as $task) {
                        if (strtolower($task['title']) === strtolower($taskTitle)) {
                            $taskId = $task['id'];
                            break;
                        }
                    }
                    
                    // If no exact match, try fuzzy matching
                    if (!$taskId) {
                        $bestMatch = $this->findBestTaskMatch($taskTitle, $tasks);
                        if ($bestMatch) {
                            // Check if this is a disambiguation response
                            if (isset($bestMatch['content']) && isset($bestMatch['data']['disambiguation'])) {
                                return $bestMatch; // Return the disambiguation response
                            }
                            $taskId = $bestMatch['id'];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Smart API updateTask - Error searching for task', ['error' => $e->getMessage()]);
            }
        }
        
        if (!$taskId) {
            return [
                'content' => "❌ **Task Not Found**\n\nI couldn't find a task with the title \"{$taskTitle}\".\n\n💡 **Suggestions:**\n• Check the spelling of the task name\n• Try a partial match (e.g., \"cursor\" instead of \"Check cursor\")\n• Ask me to list all tasks to see what's available\n• Create a new task if this one doesn't exist yet"
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->put(secure_url("/api/v1/tasks/{$taskId}"), $updateData);
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI couldn't update that task for you right now."
                ];
            }
            
            $task = $taskResponse->json()['data'] ?? [];
            
            // Format the response with more details
            $response = "✅ **Task Updated Successfully!**\n\n🟡 **{$task['title']}**";
            
            // Add assigned user info
            if (!empty($task['users']) && is_array($task['users'])) {
                $userNames = array_map(function($user) {
                    return $user['first_name'] . ' ' . $user['last_name'];
                }, $task['users']);
                $response .= "\n   └ 👤 Assigned to: " . implode(', ', $userNames);
            }
            
            // Add status info
            if (!empty($task['status']['title'])) {
                $response .= "\n   └ 📊 Status: " . $task['status']['title'];
            }
            
            // Add priority info
            if (!empty($task['priority']['title'])) {
                $response .= "\n   └ 🎯 Priority: " . $task['priority']['title'];
            }
            
            // Add due date info
            if (!empty($task['end_date'])) {
                $dueDate = \Carbon\Carbon::parse($task['end_date'])->format('M j, Y');
                $response .= "\n   └ 🗓️ Due: " . $dueDate;
            }
            
            // Add project info
            if (!empty($task['project']['title'])) {
                $response .= "\n   └ 📁 Project: " . $task['project']['title'];
            } else {
                $response .= "\n   └ 📁 Project: No Project";
            }
            
            return [
                'content' => $response,
                'data' => $task
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task updateTask Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble updating that task for you."
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble updating the task status."
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
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble updating the task priority."
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
                'content' => "❌ **Task Not Found**\n\nI couldn't find a task with the title \"{$taskTitle}\".\n\n💡 **Suggestions:**\n• Check the spelling of the task name\n• Try a partial match (e.g., \"cursor\" instead of \"Check cursor\")\n• Ask me to list all tasks to see what's available"
            ];
        }
        
        try {
            $taskResponse = $this->getHttpClient()->delete(secure_url("/api/v1/tasks/{$taskId}"));
            
            if (!$taskResponse->successful()) {
                return [
                    'content' => $this->generateConversationalResponse('api_error') . "\n\nI couldn't delete that task for you right now."
                ];
            }
            
            return [
                'content' => "✅ **Task Deleted Successfully!**"
            ];
            
        } catch (\Exception $e) {
            Log::error('Smart Task deleteTask Error', ['error' => $e->getMessage()]);
            return [
                'content' => $this->generateConversationalResponse('api_error') . "\n\nI had trouble deleting that task for you."
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
            
            $client = \OpenAI::client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.1
            ]);
            
            $aiResponse = $response->choices[0]->message->content;
            
            Log::info('Smart API OpenAI Response', [
                'response' => $aiResponse
            ]);
            
            // Try to parse the AI response as JSON
            $parsedResponse = json_decode($aiResponse, true);
            
            if ($parsedResponse && isset($parsedResponse['action'])) {
                // AI provided a structured response, execute it directly (avoid recursion)
                $action = $parsedResponse['action'];
                $params = $parsedResponse['params'] ?? [];
                
                // Execute the action directly to avoid recursive fallback calls
                switch ($action) {
                    case 'get_user_tasks':
                        return $this->getUserTasks($params);
                    case 'create_task':
                        return $this->createTask($params);
                    case 'list_tasks':
                        return $this->listTasks($params);
                    case 'get_task_status':
                        return $this->getTaskStatus($params);
                    case 'get_task_updates':
                        return $this->getTaskUpdates($params);
                    case 'add_task_message':
                        return $this->addTaskMessage($params);
                    case 'add_task_attachment':
                        return $this->addTaskAttachment($params);
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
                        // If action not recognized, return natural language response
                        return [
                            'content' => $aiResponse . "\n\n🔧 **Available Actions:**\n• `create_task` - Create a new task\n• `update_task` - Update an existing task\n• `delete_task` - Delete a task\n• `get_user_tasks` - Get tasks for a user\n• `list_tasks` - List all tasks\n• `get_projects` - List all projects\n• `get_users` - List all users\n\n💡 **Try rephrasing your request** or ask me to list available tasks/users to see what's available."
                        ];
                }
            } else {
                // AI provided a natural language response - enhance it with API suggestions
                $enhancedResponse = $aiResponse . "\n\n🔧 **Available Actions:**\n";
                $enhancedResponse .= "• `create_task` - Create a new task\n";
                $enhancedResponse .= "• `update_task` - Update an existing task\n";
                $enhancedResponse .= "• `delete_task` - Delete a task\n";
                $enhancedResponse .= "• `get_user_tasks` - Get tasks for a user\n";
                $enhancedResponse .= "• `list_tasks` - List all tasks\n";
                $enhancedResponse .= "• `get_projects` - List all projects\n";
                $enhancedResponse .= "• `get_users` - List all users\n\n";
                $enhancedResponse .= "💡 **Try rephrasing your request** or ask me to list available tasks/users to see what's available.";
                
                return [
                    'content' => $enhancedResponse,
                    'data' => ['ai_fallback' => true]
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Smart API OpenAI Fallback Error', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            
            return [
                'content' => "❌ **I'm having trouble understanding your request.**\n\n🔧 **Available Actions:**\n• `create_task` - Create a new task\n• `update_task` - Update an existing task\n• `delete_task` - Delete a task\n• `get_user_tasks` - Get tasks for a user\n• `list_tasks` - List all tasks\n• `get_projects` - List all projects\n• `get_users` - List all users\n\n💡 **Try rephrasing your request** or ask me to list available tasks/users to see what's available.\n\n🆘 **Still stuck?** Contact support if the issue persists."
            ];
        }
    }
    
    /**
     * Get the system prompt for OpenAI
     */
    private function getSystemPrompt(): string
    {
        return "You are a task management assistant. Analyze the user request and return JSON with the correct action and parameters.

ACTIONS:
- create_task: title, user_name, description, priority, due_date
- update_task: title, due_date, priority, status, description  
- delete_task: title
- get_user_tasks: user_name
- list_tasks: status, priority, user_name
- get_projects: (no params)
- get_users: (no params)

RESPONSE: Always return JSON only:
{\"action\": \"action_name\", \"params\": {\"param\": \"value\"}}

EXAMPLES:
\"create task for John to review proposal\" → {\"action\": \"create_task\", \"params\": {\"title\": \"review proposal\", \"user_name\": \"John\"}}
\"change due date for Check cursor to Oct 15\" → {\"action\": \"update_task\", \"params\": {\"title\": \"Check cursor\", \"due_date\": \"2025-10-15\"}}";
    }
    
    /**
     * Calculate the start date for recurring tasks based on the pattern
     */
    private function calculateRecurringStartDate(?string $pattern): string
    {
        $today = now();
        
        // Map day names to Carbon day constants
        $dayMap = [
            'monday' => \Carbon\Carbon::MONDAY,
            'tuesday' => \Carbon\Carbon::TUESDAY,
            'wednesday' => \Carbon\Carbon::WEDNESDAY,
            'thursday' => \Carbon\Carbon::THURSDAY,
            'friday' => \Carbon\Carbon::FRIDAY,
            'saturday' => \Carbon\Carbon::SATURDAY,
            'sunday' => \Carbon\Carbon::SUNDAY,
        ];
        
        // If pattern contains a specific day of the week
        foreach ($dayMap as $dayName => $dayConstant) {
            if (strpos($pattern, $dayName) !== false) {
                // Find the next occurrence of this day
                $nextDay = $today->copy()->next($dayConstant);
                return $nextDay->format('Y-m-d');
            }
        }
        
        // For generic weekly patterns, start next week
        if (strpos($pattern, 'weekly') !== false || strpos($pattern, 'every') !== false) {
            return $today->addWeek()->format('Y-m-d');
        }
        
        // For daily patterns, start tomorrow
        if (strpos($pattern, 'daily') !== false) {
            return $today->addDay()->format('Y-m-d');
        }
        
        // Default: start next week
        return $today->addWeek()->format('Y-m-d');
    }
}

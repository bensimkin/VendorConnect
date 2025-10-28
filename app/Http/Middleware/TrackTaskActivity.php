<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TaskUser;

class TrackTaskActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track authenticated requests
        if (Auth::check()) {
            $this->updateTaskActivity($request);
        }

        return $response;
    }

    /**
     * Update last activity timestamp for task interactions
     */
    private function updateTaskActivity(Request $request): void
    {
        try {
            // Extract task ID from route if present
            $taskId = $request->route('id') ?? $request->route('task_id');
            
            // Only track if this is a task-related endpoint
            if (!$taskId || !str_contains($request->path(), 'tasks')) {
                return;
            }

            $userId = Auth::id();

            // Check if user is assigned to this task
            $isAssigned = TaskUser::where('task_id', $taskId)
                ->where('user_id', $userId)
                ->exists();

            // Only track if user is actually assigned to the task
            // TaskUser::updateActivity will check if task is completed/archived
            if ($isAssigned) {
                TaskUser::updateActivity($taskId, $userId);
            }
                
        } catch (\Exception $e) {
            // Fail silently - don't break user actions
            \Log::debug('Task activity tracking failed', [
                'user_id' => Auth::id(),
                'path' => $request->path(),
                'error' => $e->getMessage()
            ]);
        }
    }
}

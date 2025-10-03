<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskDeliverableController;
use App\Http\Controllers\Api\TaskTypeController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\PriorityController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskBriefTemplateController;
use App\Http\Controllers\Api\TaskBriefQuestionController;
use App\Http\Controllers\Api\TaskBriefChecklistController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ClientCredentialController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\SmartTaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/auth/send-welcome-email', [AuthController::class, 'sendWelcomeEmail']);
    
    // Protected routes (authentication required)
    Route::middleware(['cAuth'])->group(function () {
        
        // User profile
        Route::get('/user', function (Request $request) {
            $user = $request->user();
            \Log::info('User endpoint called, user:', ['id' => $user->id ?? 'null', 'email' => $user->email ?? 'null']);
            
            if ($user) {
                $user->load(['roles' => function($query) {
                    $query->select('id', 'name');
                }]);
                \Log::info('User roles loaded:', ['roles' => $user->roles->pluck('name')->toArray()]);
            }
            
            return $user;
        });
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/tasker', [DashboardController::class, 'taskerDashboard']);
        Route::get('/dashboard/requester', [DashboardController::class, 'requesterDashboard']);
        
        // Profile management
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'current']);
            Route::get('/{id}', [ProfileController::class, 'show']);
            Route::put('/{id}', [ProfileController::class, 'update']);
            Route::put('/{id}/photo', [ProfileController::class, 'updatePhoto']);
        });
        
        // Tasks
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('/{id}', [TaskController::class, 'show']);
            Route::put('/{id}', [TaskController::class, 'update']);
            Route::delete('/{id}', [TaskController::class, 'destroy']);
            Route::delete('/multiple', [TaskController::class, 'destroyMultiple']);
            
            // Task status and deadline
            Route::put('/{id}/status', [TaskController::class, 'updateStatus']);
            Route::put('/{id}/priority', [TaskController::class, 'updatePriority']);
            Route::put('/{id}/deadline', [TaskController::class, 'updateDeadline']);
            
            // Task answers
            Route::get('/{id}/question-answers', [TaskController::class, 'getQuestionAnswers']);
            Route::post('/{id}/question-answer', [TaskController::class, 'submitQuestionAnswer']);
            Route::get('/{id}/checklist-answers', [TaskController::class, 'getChecklistAnswers']);
            Route::post('/{id}/checklist-answer', [TaskController::class, 'submitChecklistAnswer']);
            Route::get('/{id}/checklist-status', [TaskController::class, 'getChecklistStatus']);
            
            // Task deliverables
            // Task Deliverables
Route::get('/{taskId}/deliverables', [TaskDeliverableController::class, 'index']);
Route::post('/{taskId}/deliverables', [TaskDeliverableController::class, 'store']);
Route::get('/{taskId}/deliverables/{deliverableId}', [TaskDeliverableController::class, 'show']);
Route::put('/{taskId}/deliverables/{deliverableId}', [TaskDeliverableController::class, 'update']);
Route::delete('/{taskId}/deliverables/{deliverableId}', [TaskDeliverableController::class, 'destroy']);
Route::post('/{taskId}/deliverables/{deliverableId}/complete', [TaskDeliverableController::class, 'complete']);
Route::delete('/{taskId}/deliverables/{deliverableId}/files/{mediaId}', [TaskDeliverableController::class, 'deleteFile']);
            
            // Task media
            Route::post('/{id}/media', [TaskController::class, 'uploadMedia']);
            Route::get('/{id}/media', [TaskController::class, 'getMedia']);
            Route::delete('/media/{mediaId}', [TaskController::class, 'deleteMedia']);
            Route::delete('/media', [TaskController::class, 'deleteMultipleMedia']);
            
            // Task messages
            Route::post('/{id}/messages', [TaskController::class, 'uploadMessage']);
            Route::get('/{id}/messages', [TaskController::class, 'getMessages']);
            Route::delete('/messages/{messageId}', [TaskController::class, 'deleteMessage']);
            Route::delete('/messages', [TaskController::class, 'deleteMultipleMessages']);
            
            // Task information
            Route::get('/{id}/information', [TaskController::class, 'getInformation']);
            
            // Client brief and brand guide files (accessible on task page)
            Route::get('/{id}/client-brief-files', [TaskController::class, 'getClientBriefAndFiles']);
            Route::put('/{id}/client-brief', [TaskController::class, 'updateClientBrief']);
            Route::post('/{id}/files', [TaskController::class, 'uploadTaskFile']);
            Route::delete('/{taskId}/files/{fileId}', [TaskController::class, 'deleteTaskFile']);
            
            // Task repetition
            Route::post('/{id}/stop-repetition', [TaskController::class, 'stopRepetition']);
            Route::post('/{id}/resume-repetition', [TaskController::class, 'resumeRepetition']);
            Route::get('/{id}/repeating-history', [TaskController::class, 'getRepeatingHistory']);
        });
        
        // Projects
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectController::class, 'index']);
            Route::post('/', [ProjectController::class, 'store']);
            Route::get('/{id}', [ProjectController::class, 'show']);
            Route::put('/{id}', [ProjectController::class, 'update']);
            Route::delete('/{id}', [ProjectController::class, 'destroy']);
            Route::get('/{id}/statistics', [ProjectController::class, 'getStatistics']);
            Route::get('/{id}/tasks', [ProjectController::class, 'getTasks']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::get('/stats', [NotificationController::class, 'stats']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::post('/{id}/unread', [NotificationController::class, 'markAsUnread']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::delete('/read', [NotificationController::class, 'deleteRead']);
            Route::get('/types', [NotificationController::class, 'types']);
            Route::get('/priorities', [NotificationController::class, 'priorities']);
        });
        
        // Task Types
        Route::prefix('task-types')->group(function () {
            Route::get('/', [TaskTypeController::class, 'index']);
            Route::post('/', [TaskTypeController::class, 'store']);
            Route::get('/{id}', [TaskTypeController::class, 'show']);
            Route::put('/{id}', [TaskTypeController::class, 'update']);
            Route::delete('/{id}', [TaskTypeController::class, 'destroy']);
        });
        
        // Statuses
Route::prefix('statuses')->group(function () {
    Route::get('/', [StatusController::class, 'index']);
    Route::post('/', [StatusController::class, 'store']);
    Route::get('/{id}', [StatusController::class, 'show']);
    Route::put('/{id}', [StatusController::class, 'update']);
    Route::delete('/{id}', [StatusController::class, 'destroy']);
    Route::delete('/', [StatusController::class, 'destroyMultiple']);
});
        
        // Priorities
Route::prefix('priorities')->group(function () {
    Route::get('/', [PriorityController::class, 'index']);
    Route::post('/', [PriorityController::class, 'store']);
    Route::get('/{id}', [PriorityController::class, 'show']);
    Route::put('/{id}', [PriorityController::class, 'update']);
    Route::delete('/{id}', [PriorityController::class, 'destroy']);
    Route::delete('/', [PriorityController::class, 'destroyMultiple']);
});
        
        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
            Route::delete('/', [UserController::class, 'destroyMultiple']);
        });
        
        // User Roles
        Route::prefix('user-roles')->group(function () {
            Route::get('/', [UserRoleController::class, 'index']);
            Route::post('/', [UserRoleController::class, 'store']);
            Route::get('/{id}', [UserRoleController::class, 'show']);
            Route::put('/{id}', [UserRoleController::class, 'update']);
            Route::delete('/{id}', [UserRoleController::class, 'destroy']);
        });
        
        // Roles
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{id}', [RoleController::class, 'show']);
            Route::put('/{id}', [RoleController::class, 'update']);
            Route::delete('/{id}', [RoleController::class, 'destroy']);
        });
        
        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index']);
            Route::get('/project', [SettingsController::class, 'getProjectSettings']);
            Route::get('/group/{group}', [SettingsController::class, 'getByGroup']);
            
            // Auto-archive settings (must come before generic {key} routes)
            Route::get('/auto-archive', [SettingsController::class, 'getAutoArchiveSettings']);
            Route::put('/auto-archive', [SettingsController::class, 'updateAutoArchiveSettings']);
            
            // Generic settings routes (must come after specific routes)
            Route::get('/{key}', [SettingsController::class, 'show']);
            Route::put('/{key}', [SettingsController::class, 'update']);
        });
        
        // Clients
        Route::prefix('clients')->group(function () {
            Route::get('/', [ClientController::class, 'index']);
            Route::post('/', [ClientController::class, 'store']);
            Route::get('/{id}', [ClientController::class, 'show']);
            Route::put('/{id}', [ClientController::class, 'update']);
            Route::delete('/{id}', [ClientController::class, 'destroy']);
            Route::delete('/', [ClientController::class, 'destroyMultiple']);
            Route::delete('/files/{fileId}', [ClientController::class, 'deleteFile']);
            Route::get('/{id}/projects', [ClientController::class, 'projects']);
            Route::get('/{id}/tasks', [ClientController::class, 'tasks']);
            Route::get('/{id}/portfolio', [PortfolioController::class, 'clientPortfolio']);
            Route::get('/{id}/portfolio/stats', [PortfolioController::class, 'clientStats']);
            
            // Client Credentials
            Route::prefix('{clientId}/credentials')->group(function () {
                Route::get('/', [ClientCredentialController::class, 'index']);
                Route::post('/', [ClientCredentialController::class, 'store']);
                Route::get('/{credentialId}', [ClientCredentialController::class, 'show']);
                Route::put('/{credentialId}', [ClientCredentialController::class, 'update']);
                Route::delete('/{credentialId}', [ClientCredentialController::class, 'destroy']);
                Route::get('/{credentialId}/password', [ClientCredentialController::class, 'getPassword']);
            });
        });
        
        // Portfolios
        Route::prefix('portfolios')->group(function () {
            Route::get('/', [PortfolioController::class, 'index']);
            Route::post('/', [PortfolioController::class, 'store']);
            Route::get('/{id}', [PortfolioController::class, 'show']);
            Route::put('/{id}', [PortfolioController::class, 'update']);
            Route::delete('/{id}', [PortfolioController::class, 'destroy']);
            
            // Portfolio media
            Route::post('/{id}/media', [PortfolioController::class, 'uploadMedia']);
            Route::delete('/{id}/media/{mediaId}', [PortfolioController::class, 'deleteMedia']);
        });
        
        // Tags
        Route::prefix('tags')->group(function () {
            Route::get('/', [TagController::class, 'index']);
            Route::post('/', [TagController::class, 'store']);
            Route::get('/{id}', [TagController::class, 'show']);
            Route::put('/{id}', [TagController::class, 'update']);
            Route::delete('/{id}', [TagController::class, 'destroy']);
        });
        
        // Task Brief Templates
        Route::prefix('task-brief-templates')->group(function () {
            Route::get('/', [TaskBriefTemplateController::class, 'index']);
            Route::post('/', [TaskBriefTemplateController::class, 'store']);
            Route::get('/{id}', [TaskBriefTemplateController::class, 'show']);
            Route::put('/{id}', [TaskBriefTemplateController::class, 'update']);
            Route::delete('/{id}', [TaskBriefTemplateController::class, 'destroy']);
        });
        
        // Task Brief Questions
        Route::prefix('task-brief-questions')->group(function () {
            Route::get('/', [TaskBriefQuestionController::class, 'index']);
            Route::post('/', [TaskBriefQuestionController::class, 'store']);
            Route::get('/{id}', [TaskBriefQuestionController::class, 'show']);
            Route::put('/{id}', [TaskBriefQuestionController::class, 'update']);
            Route::delete('/{id}', [TaskBriefQuestionController::class, 'destroy']);
        });
        
        // Task Brief Checklists
        Route::prefix('task-brief-checklists')->group(function () {
            Route::get('/', [TaskBriefChecklistController::class, 'index']);
            Route::post('/', [TaskBriefChecklistController::class, 'store']);
            Route::get('/{id}', [TaskBriefChecklistController::class, 'show']);
            Route::put('/{id}', [TaskBriefChecklistController::class, 'update']);
            Route::delete('/{id}', [TaskBriefChecklistController::class, 'destroy']);
        });
        
        // Global Search
        Route::get('/search', [SearchController::class, 'globalSearch']);

        // API Keys Management
        Route::prefix('api-keys')->group(function () {
            Route::get('/', [ApiKeyController::class, 'index']);
            Route::post('/', [ApiKeyController::class, 'store']);
            Route::get('/stats', [ApiKeyController::class, 'stats']);
            Route::get('/{id}', [ApiKeyController::class, 'show']);
            Route::put('/{id}', [ApiKeyController::class, 'update']);
            Route::delete('/{id}', [ApiKeyController::class, 'destroy']);
            Route::post('/{id}/regenerate', [ApiKeyController::class, 'regenerate']);
        });

        // Smart Task API - AI-powered task management via n8n
        Route::post('/smart-task', [SmartTaskController::class, 'handleRequest']);

    });
});

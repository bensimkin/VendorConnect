<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskTypeController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\PriorityController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskBriefTemplateController;
use App\Http\Controllers\Api\TaskBriefQuestionController;
use App\Http\Controllers\Api\TaskBriefChecklistController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PortfolioController;

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
    
    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        
        // User profile
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        
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
            Route::put('/{id}/deadline', [TaskController::class, 'updateDeadline']);
            
            // Task answers
            Route::post('/{id}/question-answer', [TaskController::class, 'submitQuestionAnswer']);
            Route::post('/{id}/checklist-answer', [TaskController::class, 'submitChecklistAnswer']);
            
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
        });
        
        // Projects
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectController::class, 'index']);
            Route::post('/', [ProjectController::class, 'store']);
            Route::get('/{id}', [ProjectController::class, 'show']);
            Route::put('/{id}', [ProjectController::class, 'update']);
            Route::delete('/{id}', [ProjectController::class, 'destroy']);
            Route::get('/{id}/statistics', [ProjectController::class, 'getStatistics']);
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
        
        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        });
    });
});

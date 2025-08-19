<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\businessManger;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\forgetPassword;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\indexController;
use App\Http\Controllers\MasterAdminController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TaskBriefQuestionController;
use App\Http\Controllers\TaskBriefChecklistController;
use App\Http\Controllers\TaskBriefTemplate;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TaskTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Http\Middleware\IsLogin;
use App\Http\Middleware\IsmasterAdminLogin;
use Illuminate\Support\Facades\Route;

// ===================================
// ==============User Auth============
// ===================================
Route::post('/forgot-password-mail', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forgot-password-mail');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'ResetPassword'])->name('password.update');
Route::get('/email/verify', [UserController::class, 'email_verification'])->name('verification.notice')->middleware(['auth:web,client']);
Route::get('/email/verify/{id}/{hash}', [ClientController::class, 'verify_email'])->name('verification.verify');
Route::get('/email/verification-notification', [UserController::class, 'resend_verification_link'])->name('verification.send');
// ===========================================
// ===============FrontEnd work===============
// ===========================================
Route::get('/', [FrontEndController::class, 'LoginView'])->name('login.view');
Route::post('/users/authenticate', [UserController::class, 'authenticate'])->name('users.authenticate');
Route::middleware(['checkAccess'])->group(function () {
    Route::get('/profile/{id}', [FrontEndController::class, 'UserProfile'])->name('user.profile.view');
    Route::get('/dashboard', [FrontEndController::class, 'DashboardView'])->name('dashboard.view');
    Route::get('/forgot-password', [FrontEndController::class, 'ForgotPasswordView'])->name('forgot.pass.view');
    Route::get('/tasks', [FrontEndController::class, 'TaskView'])->name('task.view');
    Route::get('/task-type', [FrontEndController::class, 'TaskTypeView'])->name('tasktype.view');
    Route::get('/users', [FrontEndController::class, 'UsersView'])->name('user.view');
    Route::get('/add-user', [FrontEndController::class, 'AddUsersView'])->name('add.user.view');
    Route::get('/status', [FrontEndController::class, 'statusesView'])->name('statuses.view');
    Route::get('/priority', [FrontEndController::class, 'priorityView'])->name('priority.view');
    Route::get('/user-role', [FrontEndController::class, 'UserRoleView'])->name('user.role.view');
    Route::get('/clients', [FrontEndController::class, 'ClientsView'])->name('clients.view');
    Route::get('/add-client', [FrontEndController::class, 'AddClients'])->name('add.clients.view');
    Route::get('/tags', [FrontEndController::class, 'TagsView'])->name('tags.view');
    Route::get('/notifications', [FrontEndController::class, 'NotificationsView'])->name('notification.view');
    Route::get('/task-brief-template', [FrontEndController::class, 'TaskBreifTemplate'])->name('task.breif.view');
    Route::get('/task-brief-question', [FrontEndController::class, 'TaskBreifQuestion'])->name('task.breif.question.view');
    Route::get('/task/information/{id}', [FrontEndController::class, 'TaskInformationView'])->name('task.information.view');
    Route::post('/task/question_answer', [TasksController::class, 'TaskAnswerSubmit'])->name('task.question_answer');
    Route::post('/task/checklist_answer', [TasksController::class, 'TaskCheckListAnswer'])->name('task.checklist_answer');
    Route::post('/tasks/update-status', [TasksController::class, 'updateStatus'])->name('tasks.update_status');
    Route::post('/tasks/update-deadline', [TasksController::class, 'updatedeadline'])->name('tasks.update_deadline');
    // Task Check List
    Route::get('/check-brief-item', [FrontEndController::class, 'ViewCheckBrief'])->name('view.check.list');

    // ===========================================
    // ===============Backend work===============
    // ===========================================
    Route::post('/status/store', [StatusController::class, 'store'])->name('status.store');
    Route::get('/status/list', [StatusController::class, 'list'])->name('status.list');
    Route::post('/status/update', [StatusController::class, 'update'])->name('status.update');
    Route::get('/status/get/{id}', [StatusController::class, 'get'])->name('status.get');
    Route::delete('/status/destroy/{id}', [StatusController::class, 'destroy'])->name('status.destroy');
    Route::delete('/status/destroy_multiple', [StatusController::class, 'destroy_multiple'])->name('status.destroy_multiple');
    // Priorities
    Route::post('/priority/store', [PriorityController::class, 'store'])->name('priority.store');
    Route::post('/priority/update', [PriorityController::class, 'update'])->name('priority.update');
    Route::delete('/priority/destroy/{id}', [PriorityController::class, 'destroy'])->name('priority.destroy');
    Route::get('/priority/get/{id}', [PriorityController::class, 'get'])->name('priority.get');
    Route::delete('/priority/destroy_multiple', [PriorityController::class, 'destroy_multiple'])->name('priority.destroy_multiple');
    Route::get('/priority/list', [PriorityController::class, 'list'])->name('priority.list');
    // user roles
    Route::post('/user/role', [UserRoleController::class, 'addRole'])->name('add.role');
    Route::get('/user-role/get/{id}', [UserRoleController::class, 'get'])->name('user.list.get');
    Route::get('/user-role/list', [UserRoleController::class, 'list'])->name('user.role.list');
    Route::post('/user-role/update', [UserRoleController::class, 'update'])->name('user.role.update');
    Route::delete('/user-role/destroy/{id}', [UserRoleController::class, 'destroy'])->name('user.role.destroy');
    // Tags
    Route::post('/tags/store', [TagsController::class, 'store'])->name('tags.store');
    Route::get('/tags/list', [TagsController::class, 'list'])->name('tags.list');
    Route::get('/tags/get/{id}', [TagsController::class, 'get'])->name('tags.get');
    Route::post('/tags/update', [TagsController::class, 'update'])->name('tags.update');
    Route::delete('/tags/destroy/{id}', [TagsController::class, 'destroy'])->name('tags.destroy');
    // Task Types
    Route::post('/task-type/store', [TaskTypeController::class, 'store'])->name('task.type.store');
    Route::get('/task-type/list', [TaskTypeController::class, 'list'])->name('task.type.list');
    Route::get('/task-type/get/{id}', [TaskTypeController::class, 'get'])->name('task.type.get');
    Route::post('/task-type/update', [TaskTypeController::class, 'update'])->name('task.type.update');
    Route::delete('/task-type/destroy/{id}', [TaskTypeController::class, 'destroy'])->name('task.type.destroy');
    // Task Brief Template
    Route::post('/task-brief-template/store', [TaskBriefTemplate::class, 'store'])->name('task.brief.store');
    Route::get('/task-brief-template/list', [TaskBriefTemplate::class, 'list'])->name('task.brief.template.list');
    Route::get('/task-brief-template/get/{id}', [TaskBriefTemplate::class, 'get'])->name('task.brief.get');
    Route::post('/task-brief-template/update', [TaskBriefTemplate::class, 'update'])->name('task.brief.update');
    Route::delete('/task-brief-template/destroy/{id}', [TaskBriefTemplate::class, 'destroy'])->name('task.brief.destroy');
    // Task Brief Question
    Route::post('/task-brief-question/store', [TaskBriefQuestionController::class, 'store'])->name('task.question.store');
    Route::get('/task-brief-question/list', [TaskBriefQuestionController::class, 'list'])->name('task.question.template.list');
    Route::get('/task-brief-question/get/{id}', [TaskBriefQuestionController::class, 'get'])->name('task.question.get');
    Route::post('/task-brief-question/update', [TaskBriefQuestionController::class, 'update'])->name('task.question.update');
    Route::delete('/task-brief-question/destroy/{id}', [TaskBriefQuestionController::class, 'destroy'])->name('task.question.destroy');
    // Task Brief CheckList
    Route::post('/task-brief-checklist/store', [TaskBriefChecklistController::class, 'store'])->name('task.checklist.store');
    Route::get('/task-brief-checklist/list', [TaskBriefChecklistController::class, 'list'])->name('task.checklist.template.list');
    Route::get('/task-brief-checklist/get/{id}', [TaskBriefChecklistController::class, 'get'])->name('task.checklist.get');
    Route::post('/task-brief-checklist/update', [TaskBriefChecklistController::class, 'update'])->name('task.checklist.update');
    Route::delete('/check-brief-item/destroy/{id}', [TaskBriefChecklistController::class, 'destroy'])->name('task.checklist.destroy');
    // Client
    Route::post('/clients/store', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/list', [ClientController::class, 'list'])->name('clients.list');
    Route::get('/clients/profile/{id}', [ClientController::class, 'show'])->name('clients.profile');
    Route::get('/clients/edit/{id}', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/update', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/destroy/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::delete('clients/clients/destroy_multiple', [ClientController::class, 'destroy_multiple'])->name('clients.destroy_multiple');
    Route::delete('/files/{fileId}', [ClientController::class, 'deleteFile'])->name('client.deleteFile');
    // Users
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/list', [UserController::class, 'list'])->name('users.list');
    Route::get('/users/edit/{id}', [UserController::class, 'edit_user'])->name('users.edit');
    Route::get('/users/profile/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/update_user/{user}', [UserController::class, 'update_user'])->name('users.update_user');
    Route::delete('/users/delete_user/{user}', [UserController::class, 'delete_user'])->name('users.delete_user');
    Route::delete('users/users/delete_multiple_user', [UserController::class, 'delete_multiple_user'])->name('users.delete_multiple_user');
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    // Profile Work
    Route::put('/profile/update_photo/{id}', [ProfileController::class, 'update_photo'])->name('profile.update_photo');
    Route::put('profile/update/{id}', [ProfileController::class, 'update'])->name('profile.update');
    // Tasks
    Route::post('/tasks/store', [TasksController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/list', [TasksController::class, 'list'])->name('tasks.list');
    Route::get('/tasks/get/{id}', [TasksController::class, 'get'])->name('tasks.get');
    Route::put('/tasks/update', [TasksController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/destroy/{id}', [TasksController::class, 'destroy'])->name('tasks.destroy');
    Route::delete('/tasks/tasks/destroy_multiple', [TasksController::class, 'destroy_multiple'])->name('tasks.destroy_multiple');


    Route::post('/tasks/upload-media', [TasksController::class, 'upload_media'])->name('tasks.upload_media');
    Route::get('/tasks/get-media/{id}', [TasksController::class, 'get_media'])->name('tasks.get_media');
    Route::delete('/task/delete-media/{id}', [TasksController::class, 'delete_media'])->name('tasks.delete_media');
    Route::delete('/tasks/delete-multiple-media', [TasksController::class, 'delete_multiple_media'])->name('tasks.delete_multiple_media');

    Route::post('/tasks/upload-message', [TasksController::class, 'upload_message'])->name('tasks.upload_message');
    Route::get('/tasks/get-message/{id}', [TasksController::class, 'get_message'])->name('tasks.get_message');
    Route::delete('/task/delete-message/{id}', [TasksController::class, 'delete_message'])->name('tasks.delete_message');
    Route::delete('/tasks/delete-multiple-message', [TasksController::class, 'delete_multiple_message'])->name('tasks.delete_multiple_message');
    Route::delete('/tasks/destroy_multiple', [TasksController::class, 'destroy_multiple'])->name('tasks.destroy_multiple');
});

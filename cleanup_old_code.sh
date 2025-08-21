#!/bin/bash
# VendorConnect Code Cleanup Script
# Removes old controllers, routes, and unused code

echo "🧹 VendorConnect Code Cleanup"
echo "============================="
echo ""

# Create backup directory
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
echo "📦 Creating backup directory: $BACKUP_DIR"
mkdir -p $BACKUP_DIR

# Phase 1: Backup and remove unused controllers (dead code)
echo ""
echo "🔍 Phase 1: Removing unused controllers (dead code)..."
echo "=================================================="

UNUSED_CONTROLLERS=(
    "EstimatesInvoicesController.php"
    "PaymentsController.php"
    "ExpensesController.php"
    "LeaveRequestController.php"
    "ContractsController.php"
    "MeetingsController.php"
    "TimeTrackerController.php"
    "TodosController.php"
    "UnitsController.php"
    "UpdaterController.php"
    "SubscriptionPlan.php"
    "PayslipsController.php"
    "PreferenceController.php"
    "RolesController.php"
    "SearchController.php"
    "SettingsController.php"
    "InstallerController.php"
    "ItemsController.php"
    "LanguageController.php"
    "NotesController.php"
    "PaymentMethodsController.php"
    "DeductionsController.php"
    "AllowancesController.php"
    "ActivityLogController.php"
    "HomeController.php"
    "WorkspacesController.php"
    "TaxesController.php"
)

for controller in "${UNUSED_CONTROLLERS[@]}"; do
    if [ -f "app/Http/Controllers/$controller" ]; then
        echo "🗑️  Removing unused controller: $controller"
        mv "app/Http/Controllers/$controller" "$BACKUP_DIR/"
    else
        echo "⚠️  Controller not found: $controller"
    fi
done

# Phase 2: Backup old controllers that are replaced by API
echo ""
echo "🔄 Phase 2: Backing up old controllers (replaced by API)..."
echo "========================================================"

OLD_CONTROLLERS=(
    "TasksController.php"
    "UserController.php"
    "ClientController.php"
    "StatusController.php"
    "PriorityController.php"
    "TagsController.php"
    "TaskTypeController.php"
    "UserRoleController.php"
    "TaskBriefTemplate.php"
    "TaskBriefQuestionController.php"
    "TaskBriefChecklistController.php"
    "ProfileController.php"
    "NotificationsController.php"
)

for controller in "${OLD_CONTROLLERS[@]}"; do
    if [ -f "app/Http/Controllers/$controller" ]; then
        echo "📦 Backing up old controller: $controller"
        mv "app/Http/Controllers/$controller" "$BACKUP_DIR/"
    else
        echo "⚠️  Controller not found: $controller"
    fi
done

# Phase 3: Clean up old web routes
echo ""
echo "🛣️  Phase 3: Cleaning up old web routes..."
echo "========================================="

# Create backup of current web routes
cp routes/web.php "$BACKUP_DIR/web_routes_backup.php"

# Create new clean web routes file
cat > routes/web.php << 'EOF'
<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
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
    // Frontend views only - all CRUD operations moved to API
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
    Route::get('/check-brief-item', [FrontEndController::class, 'ViewCheckBrief'])->name('view.check.list');
    
    // Keep only essential authentication routes
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
});

// ===========================================
// ===============API Routes==================
// ===========================================
// All CRUD operations are now handled by API routes in routes/api.php
// Frontend will use these API endpoints instead of direct controller calls
EOF

echo "✅ Web routes cleaned up - only frontend views and auth remain"

# Phase 4: Clean up old views (optional - keep for now)
echo ""
echo "👁️  Phase 4: Frontend views status..."
echo "===================================="
echo "📁 Old views are kept for now (in resources/views/front-end/)"
echo "🔄 These will be replaced when you build the new frontend"
echo "💡 You can delete them after the new frontend is working"

# Phase 5: Create cleanup summary
echo ""
echo "📋 Phase 5: Creating cleanup summary..."
echo "======================================"

cat > "$BACKUP_DIR/cleanup_summary.md" << EOF
# VendorConnect Code Cleanup Summary

## Date: $(date)

## What was removed:
### Unused Controllers (Dead Code):
$(for controller in "${UNUSED_CONTROLLERS[@]}"; do echo "- $controller"; done)

### Old Controllers (Replaced by API):
$(for controller in "${OLD_CONTROLLERS[@]}"; do echo "- $controller"; done)

## What was kept:
- API controllers (app/Http/Controllers/Api/)
- API routes (routes/api.php)
- Models (app/Models/)
- Migrations (database/migrations/)
- Frontend views (temporarily - for new frontend development)
- Authentication controllers
- Core Laravel files

## Next steps:
1. Test the API endpoints
2. Build new frontend using the API
3. Remove old frontend views after new frontend is working
4. Deploy to production

## Backup location:
All removed files are backed up in: $BACKUP_DIR/
EOF

echo "✅ Cleanup summary created: $BACKUP_DIR/cleanup_summary.md"

# Final summary
echo ""
echo "🎉 CLEANUP COMPLETE!"
echo "==================="
echo ""
echo "📊 Summary:"
echo "  ✅ Removed $(ls $BACKUP_DIR/*.php | wc -l) old controllers"
echo "  ✅ Cleaned up web routes"
echo "  ✅ Created backup in: $BACKUP_DIR/"
echo ""
echo "🚀 Next Steps:"
echo "  1. Test the API: php test_api.php"
echo "  2. Build new frontend using the API"
echo "  3. Deploy to production"
echo ""
echo "📁 Backup location: $BACKUP_DIR/"
echo "📋 Summary file: $BACKUP_DIR/cleanup_summary.md"
echo ""
echo "💡 The application is now much cleaner and ready for the new frontend!"

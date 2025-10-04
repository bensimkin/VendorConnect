# Phase 0: Data Collection Infrastructure Implementation Guide

**Project:** VendorConnect AI Project Manager  
**Phase:** Phase 0 - Data Collection Prerequisites  
**Version:** 1.0  
**Date:** September 30, 2025  
**Estimated Duration:** 3 weeks  
**Risk Level:** Medium (database changes, new tracking)

---

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Week 1: Task Engagement Tracking](#week-1-task-engagement-tracking)
4. [Week 2: Task Rejection & Activity Tracking](#week-2-task-rejection--activity-tracking)
5. [Week 3: Platform Usage & Historical Baselines](#week-3-platform-usage--historical-baselines)
6. [Testing Guide](#testing-guide)
7. [Deployment Guide](#deployment-guide)
8. [Rollback Procedures](#rollback-procedures)
9. [Verification & Monitoring](#verification--monitoring)
10. [Troubleshooting](#troubleshooting)

---

## Overview

### What Are We Building?

VendorConnect currently does NOT collect the data needed for AI Project Manager analysis. This phase builds the tracking infrastructure to capture:

- **Task View Tracking** - When users open/view tasks
- **Task Rejection Tracking** - When users reject tasks (with reasons)
- **Platform Usage Metrics** - Login sessions, time on platform, activity patterns
- **Historical Baselines** - Average project durations for comparison
- **User Activity Timestamps** - Last activity per user per task

### What We're NOT Touching

⚠️ **DO NOT MODIFY:**
- Existing task functionality
- Existing project functionality  
- Existing user authentication (except adding session tracking)
- Any business logic
- Existing database tables (except for 1 column addition to `task_user`)

### Architecture Principles

1. **Additive Only** - Only add new tables, columns, and code
2. **Non-Blocking** - Tracking should never prevent user actions
3. **Fail Silently** - If tracking fails, log error but continue
4. **Minimal Performance Impact** - Use async where possible
5. **Easy Rollback** - Can be disabled without breaking existing features

---

## Prerequisites

### Required Access

- [ ] Database access with CREATE TABLE privileges
- [ ] Backend repository write access
- [ ] Frontend repository write access
- [ ] Staging/dev environment access
- [ ] Production deployment permissions

### Required Tools

- [ ] PHP 8.1+
- [ ] Laravel 9+ (check current VendorConnect version)
- [ ] MySQL 8.0+
- [ ] Node.js 18+ (for frontend)
- [ ] Git for version control

### Required Knowledge

- [ ] Laravel migrations
- [ ] Laravel models and relationships
- [ ] Laravel middleware
- [ ] Next.js frontend development
- [ ] Database indexing strategies

### Backup Requirements

⚠️ **CRITICAL: Complete these BEFORE starting any work**

```bash
# 1. Backup production database
mysqldump -u [user] -p vendorconnect > backup_before_phase0_$(date +%Y%m%d).sql

# 2. Create git branch for all changes
git checkout -b feature/phase0-data-tracking

# 3. Backup .env file
cp .env .env.backup.phase0

# 4. Document current schema
mysqldump -u [user] -p --no-data vendorconnect > schema_before_phase0.sql
```

---

## Week 1: Task Engagement Tracking

### Goal
Track when users view tasks to identify unopened tasks and engagement patterns.

---

### Day 1-2: Database Migration for `task_views`

**Step 1: Create Migration**

```bash
cd /Users/benjaminsimkin/VendorConnect/VendorConnect
php artisan make:migration create_task_views_table
```

**Step 2: Edit Migration File**

File: `database/migrations/YYYY_MM_DD_XXXXXX_create_task_views_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('viewed_at')->useCurrent();
            $table->unsignedInteger('view_duration_seconds')->nullable()->comment('Optional: how long they viewed');
            
            // Foreign keys
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['task_id', 'user_id'], 'idx_task_user');
            $table->index('viewed_at', 'idx_viewed_at');
            $table->index(['user_id', 'viewed_at'], 'idx_user_views');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_views');
    }
};
```

**Step 3: Test Migration Locally**

```bash
# Run migration on local/dev database
php artisan migrate

# Verify table created
mysql -u root -p vendorconnect -e "DESCRIBE task_views;"

# Check indexes
mysql -u root -p vendorconnect -e "SHOW INDEX FROM task_views;"
```

**Step 4: Rollback Test**

```bash
# Test rollback works
php artisan migrate:rollback --step=1

# Verify table deleted
mysql -u root -p vendorconnect -e "SHOW TABLES LIKE 'task_views';"

# Re-run migration
php artisan migrate
```

---

### Day 3: Create TaskView Model

**Step 1: Create Model**

```bash
php artisan make:model TaskView
```

**Step 2: Edit Model**

File: `app/Models/TaskView.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskView extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'viewed_at',
        'view_duration_seconds',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'view_duration_seconds' => 'integer',
    ];

    /**
     * Get the task that was viewed
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who viewed the task
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a task view (static helper method)
     */
    public static function logView(int $taskId, int $userId): void
    {
        try {
            self::create([
                'task_id' => $taskId,
                'user_id' => $userId,
                'viewed_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fail silently but log error
            \Log::error('Failed to log task view', [
                'task_id' => $taskId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if user has viewed task
     */
    public static function hasUserViewed(int $taskId, int $userId): bool
    {
        return self::where('task_id', $taskId)
                   ->where('user_id', $userId)
                   ->exists();
    }

    /**
     * Get first view timestamp for task by user
     */
    public static function getFirstView(int $taskId, int $userId): ?self
    {
        return self::where('task_id', $taskId)
                   ->where('user_id', $userId)
                   ->orderBy('viewed_at', 'asc')
                   ->first();
    }
}
```

**Step 3: Update Task Model Relationship**

File: `app/Models/Task.php`

Add this relationship method:

```php
/**
 * Get all views for this task
 */
public function taskViews()
{
    return $this->hasMany(TaskView::class);
}

/**
 * Check if a user has viewed this task
 */
public function hasBeenViewedBy(int $userId): bool
{
    return TaskView::hasUserViewed($this->id, $userId);
}

/**
 * Get users who haven't viewed this task
 */
public function usersWhoHaventViewed()
{
    return $this->users()
                ->whereNotIn('users.id', function($query) {
                    $query->select('user_id')
                          ->from('task_views')
                          ->where('task_id', $this->id);
                });
}
```

---

### Day 4: Backend API Endpoint

**Step 1: Add Route**

File: `routes/api.php`

```php
// Task tracking routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tasks/{id}/track-view', [TaskController::class, 'trackView']);
});
```

**Step 2: Add Controller Method**

File: `app/Http/Controllers/Api/TaskController.php`

Add this method to existing TaskController:

```php
/**
 * Track task view by user
 * 
 * @param int $id Task ID
 * @return \Illuminate\Http\JsonResponse
 */
public function trackView($id)
{
    try {
        $task = Task::find($id);
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Log the view (will fail silently if error)
        TaskView::logView($task->id, $user->id);
        
        // Always return success even if logging failed
        // This ensures tracking never blocks user functionality
        return response()->json([
            'success' => true,
            'message' => 'Task view tracked'
        ]);
        
    } catch (\Exception $e) {
        // Log error but return success
        \Log::error('Task view tracking error', [
            'task_id' => $id,
            'user_id' => Auth::id(),
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Task view tracked'
        ]);
    }
}
```

**Step 3: Test API Endpoint**

```bash
# Test with curl (replace TOKEN and TASK_ID)
curl -X POST https://vc.themastermind.com.au/api/tasks/117/track-view \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"

# Expected response:
# {"success":true,"message":"Task view tracked"}

# Verify in database
mysql -u root -p vendorconnect -e "SELECT * FROM task_views ORDER BY id DESC LIMIT 5;"
```

---

### Day 5: Frontend Tracking Implementation

**Step 1: Update Task Detail Page**

File: `vendorconnect-frontend/src/app/tasks/[id]/page.tsx`

Add tracking when task is loaded:

```typescript
// Add this import at the top
import { useEffect, useRef } from 'react';

// Inside the component, add this effect
const hasTrackedView = useRef(false);

useEffect(() => {
  // Track task view only once per page load
  if (task && !hasTrackedView.current) {
    trackTaskView(task.id);
    hasTrackedView.current = true;
  }
}, [task?.id]);

// Add tracking function
const trackTaskView = async (taskId: number) => {
  try {
    // Non-blocking API call
    apiClient.post(`/tasks/${taskId}/track-view`).catch((error) => {
      // Fail silently - tracking errors should not impact user
      console.debug('Task view tracking failed:', error);
    });
  } catch (error) {
    // Fail silently
    console.debug('Task view tracking error:', error);
  }
};
```

**Step 2: Test Frontend Tracking**

1. Open browser with dev tools (Network tab)
2. Navigate to a task detail page
3. Look for POST request to `/api/tasks/{id}/track-view`
4. Verify response is 200 OK
5. Check database for new row in `task_views`

---

### Day 6: Comment Activity Indexing

**Goal:** Ensure comment timestamps can be efficiently queried for trend analysis.

**Step 1: Check Existing Indexes**

```bash
mysql -u root -p vendorconnect -e "SHOW INDEX FROM ch_messages;"
```

**Step 2: Add Index if Missing**

File: Create migration `database/migrations/YYYY_MM_DD_XXXXXX_add_indexes_to_ch_messages.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ch_messages', function (Blueprint $table) {
            // Add index for task messages by date (if not exists)
            if (!$this->indexExists('ch_messages', 'idx_task_created')) {
                $table->index(['to_id', 'created_at'], 'idx_task_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ch_messages', function (Blueprint $table) {
            $table->dropIndex('idx_task_created');
        });
    }
    
    private function indexExists($table, $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return !empty($indexes);
    }
};
```

**Step 3: Run Migration**

```bash
php artisan migrate
```

---

### Week 1: Testing Checklist

- [ ] `task_views` table created successfully
- [ ] TaskView model methods work correctly
- [ ] API endpoint returns 200 even with errors
- [ ] Frontend tracks views on task detail page load
- [ ] Database shows view records with correct timestamps
- [ ] Multiple views by same user are logged (not deduplicated)
- [ ] Performance: Page load time unchanged (<50ms difference)
- [ ] Error case: Tracking failure doesn't break task viewing

---

## Week 2: Task Rejection & Activity Tracking

### Goal
Track task rejections and last activity timestamps per user per task.

---

### Day 7-8: Task Rejection Tracking

**Step 1: Create Migration**

```bash
php artisan make:migration create_task_rejections_table
```

File: `database/migrations/YYYY_MM_DD_XXXXXX_create_task_rejections_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_rejections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->string('reason')->nullable()->comment('Brief reason code');
            $table->text('rejection_note')->nullable()->comment('Detailed note from user');
            $table->timestamp('rejected_at')->useCurrent();
            
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->index(['task_id', 'rejected_at'], 'idx_task_rejected');
            $table->index(['user_id', 'rejected_at'], 'idx_user_rejections');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_rejections');
    }
};
```

**Step 2: Create Model**

```bash
php artisan make:model TaskRejection
```

File: `app/Models/TaskRejection.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRejection extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'reason',
        'rejection_note',
        'rejected_at',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a task rejection
     */
    public static function logRejection(
        int $taskId, 
        int $userId, 
        ?string $reason = null, 
        ?string $note = null
    ): void {
        try {
            self::create([
                'task_id' => $taskId,
                'user_id' => $userId,
                'reason' => $reason,
                'rejection_note' => $note,
                'rejected_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log task rejection', [
                'task_id' => $taskId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get rejection count for user in time period
     */
    public static function getUserRejectionCount(int $userId, int $days = 30): int
    {
        return self::where('user_id', $userId)
                   ->where('rejected_at', '>=', now()->subDays($days))
                   ->count();
    }
}
```

**Step 3: Add API Endpoint**

File: `routes/api.php`

```php
Route::post('/tasks/{id}/reject', [TaskController::class, 'rejectTask']);
```

File: `app/Http/Controllers/Api/TaskController.php`

```php
/**
 * Reject a task (user declines assignment)
 * 
 * @param int $id Task ID
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function rejectTask($id, Request $request)
{
    try {
        $task = Task::find($id);
        
        if (!$task) {
            return $this->sendNotFound('Task not found');
        }

        $user = Auth::user();
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
            'rejection_note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Log rejection
        TaskRejection::logRejection(
            $task->id, 
            $user->id,
            $request->input('reason'),
            $request->input('rejection_note')
        );

        // Remove user from task assignment
        $task->users()->detach($user->id);

        // Send notification to task creator (optional)
        // You can add notification logic here

        return $this->sendResponse([], 'Task rejected successfully');
        
    } catch (\Exception $e) {
        \Log::error('Task rejection error', [
            'task_id' => $id,
            'user_id' => Auth::id(),
            'error' => $e->getMessage()
        ]);
        
        return $this->sendServerError('Error rejecting task: ' . $e->getMessage());
    }
}
```

**Step 4: Frontend UI (Optional)**

File: `vendorconnect-frontend/src/app/tasks/[id]/page.tsx`

Add reject button (only show if user is assigned and task is not completed):

```typescript
const handleRejectTask = async () => {
  if (!confirm('Are you sure you want to reject this task?')) return;

  try {
    await apiClient.post(`/tasks/${task.id}/reject`, {
      reason: 'workload', // or get from modal
      rejection_note: '' // optional
    });
    
    toast.success('Task rejected');
    router.push('/tasks');
  } catch (error) {
    console.error('Failed to reject task:', error);
    toast.error('Failed to reject task');
  }
};

// Add button in UI
{canRejectTask && (
  <button
    onClick={handleRejectTask}
    className="px-4 py-2 text-red-600 border border-red-600 rounded hover:bg-red-50"
  >
    Reject Task
  </button>
)}
```

---

### Day 9-10: Last Activity Tracking

**Step 1: Update `task_user` Table**

```bash
php artisan make:migration add_last_activity_to_task_user_table
```

File: `database/migrations/YYYY_MM_DD_XXXXXX_add_last_activity_to_task_user_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_user', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('user_id');
            $table->index('last_activity_at', 'idx_last_activity');
        });
    }

    public function down(): void
    {
        Schema::table('task_user', function (Blueprint $table) {
            $table->dropIndex('idx_last_activity');
            $table->dropColumn('last_activity_at');
        });
    }
};
```

**Step 2: Create Middleware to Track Activity**

```bash
php artisan make:middleware TrackTaskActivity
```

File: `app/Http/Middleware/TrackTaskActivity.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackTaskActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
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

            // Update last_activity_at in task_user pivot table
            DB::table('task_user')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update(['last_activity_at' => now()]);
                
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
```

**Step 3: Register Middleware**

File: `app/Http/Kernel.php`

Add to `$middlewareAliases` array:

```php
protected $middlewareAliases = [
    // ... existing middleware
    'track.task.activity' => \App\Http\Middleware\TrackTaskActivity::class,
];
```

**Step 4: Apply Middleware to Routes**

File: `routes/api.php`

```php
// Apply middleware to task-related routes
Route::middleware(['auth:sanctum', 'track.task.activity'])->group(function () {
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::post('/tasks/{id}/deliverables', [TaskController::class, 'addDeliverable']);
    Route::post('/tasks/{id}/comments', [TaskController::class, 'addComment']);
    // ... other task routes
});
```

---

### Day 11-12: Testing & Refinement

**Test Activity Tracking:**

```bash
# Query to check activity updates
mysql -u root -p vendorconnect << EOF
SELECT 
    tu.task_id,
    tu.user_id,
    u.first_name,
    u.last_name,
    tu.last_activity_at,
    TIMESTAMPDIFF(HOUR, tu.last_activity_at, NOW()) as hours_since_activity
FROM task_user tu
JOIN users u ON tu.user_id = u.id
WHERE tu.last_activity_at IS NOT NULL
ORDER BY tu.last_activity_at DESC
LIMIT 10;
EOF
```

**Performance Test:**

```bash
# Measure middleware overhead
ab -n 100 -c 10 -H "Authorization: Bearer TOKEN" \
   https://vc.themastermind.com.au/api/tasks/117
```

---

### Week 2: Testing Checklist

- [ ] `task_rejections` table created
- [ ] Task rejection API endpoint works
- [ ] Rejection removes user from task
- [ ] `task_user.last_activity_at` column added
- [ ] Middleware updates activity timestamp
- [ ] Activity tracking doesn't slow down requests (<10ms overhead)
- [ ] Frontend reject button works (if implemented)
- [ ] Rejection count queries work correctly

---

## Week 3: Platform Usage & Historical Baselines

### Goal
Track user sessions and calculate historical project metrics for baseline comparisons.

---

### Day 13-14: User Session Tracking

**Step 1: Create Migration**

```bash
php artisan make:migration create_user_sessions_table
```

File: `database/migrations/YYYY_MM_DD_XXXXXX_create_user_sessions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('session_token', 255)->unique();
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Calculated on logout');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->index(['user_id', 'login_at'], 'idx_user_sessions');
            $table->index('logout_at', 'idx_active_sessions');
            $table->index('last_activity_at', 'idx_activity');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
```

**Step 2: Create Model**

```bash
php artisan make:model UserSession
```

File: `app/Models/UserSession.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_token',
        'login_at',
        'logout_at',
        'last_activity_at',
        'duration_seconds',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Start a new session
     */
    public static function startSession(int $userId, string $token, string $ipAddress = null, string $userAgent = null): self
    {
        try {
            return self::create([
                'user_id' => $userId,
                'session_token' => $token,
                'login_at' => now(),
                'last_activity_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to start user session', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * End a session
     */
    public function endSession(): void
    {
        try {
            $this->logout_at = now();
            $this->duration_seconds = $this->login_at->diffInSeconds($this->logout_at);
            $this->save();
        } catch (\Exception $e) {
            \Log::error('Failed to end user session', [
                'session_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        try {
            $this->last_activity_at = now();
            $this->save();
        } catch (\Exception $e) {
            \Log::debug('Failed to update session activity', [
                'session_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get active sessions (not logged out)
     */
    public static function getActiveSessions()
    {
        return self::whereNull('logout_at')->get();
    }

    /**
     * Get user's average session duration
     */
    public static function getUserAverageSessionDuration(int $userId): ?int
    {
        return self::where('user_id', $userId)
                   ->whereNotNull('duration_seconds')
                   ->avg('duration_seconds');
    }

    /**
     * Close stale sessions (no activity in 24 hours)
     */
    public static function closeStaleSessions(): int
    {
        $staleSessions = self::whereNull('logout_at')
            ->where('last_activity_at', '<', now()->subHours(24))
            ->get();

        foreach ($staleSessions as $session) {
            $session->endSession();
        }

        return $staleSessions->count();
    }
}
```

**Step 3: Update Login to Track Session**

File: `app/Http/Controllers/Api/AuthController.php`

Update the `login` method:

```php
public function login(Request $request)
{
    // ... existing validation and authentication code ...

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials!'
        ], 401);
    }

    // ... existing status check ...

    // Create token with expiration
    $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

    // Update last login timestamp
    $user->update(['last_login_at' => now()]);

    // START SESSION TRACKING (NEW CODE)
    try {
        UserSession::startSession(
            $user->id,
            $token,
            $request->ip(),
            $request->userAgent()
        );
    } catch (\Exception $e) {
        // Don't fail login if session tracking fails
        \Log::error('Session tracking failed on login', [
            'user_id' => $user->id,
            'error' => $e->getMessage()
        ]);
    }
    // END SESSION TRACKING

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'user' => $user,
            'token' => $token,
        ]
    ]);
}
```

**Step 4: Update Logout to End Session**

File: `app/Http/Controllers/Api/AuthController.php`

```php
public function logout(Request $request)
{
    try {
        $user = Auth::user();
        $token = $request->bearerToken();

        // End session tracking
        if ($token) {
            $session = UserSession::where('session_token', $token)->first();
            if ($session) {
                $session->endSession();
            }
        }

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    } catch (\Exception $e) {
        \Log::error('Logout error', [
            'user_id' => Auth::id(),
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Logout failed'
        ], 500);
    }
}
```

**Step 5: Create Console Command to Close Stale Sessions**

```bash
php artisan make:command CloseStaleSessions
```

File: `app/Console/Commands/CloseStaleSessions.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSession;

class CloseStaleSessions extends Command
{
    protected $signature = 'sessions:close-stale';
    protected $description = 'Close user sessions with no activity in 24 hours';

    public function handle()
    {
        $this->info('Closing stale sessions...');
        
        $count = UserSession::closeStaleSessions();
        
        $this->info("Closed {$count} stale sessions.");
        
        return 0;
    }
}
```

**Step 6: Schedule Command**

File: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Close stale sessions daily at 2 AM
    $schedule->command('sessions:close-stale')->dailyAt('02:00');
}
```

---

### Day 15-16: Historical Project Baselines

**Step 1: Create Migration**

```bash
php artisan make:migration create_project_metrics_baseline_table
```

File: `database/migrations/YYYY_MM_DD_XXXXXX_create_project_metrics_baseline_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_metrics_baseline', function (Blueprint $table) {
            $table->id();
            $table->string('project_type', 100)->nullable()->comment('Optional: project category');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('metric_name', 100);
            $table->decimal('metric_value', 10, 2);
            $table->unsignedInteger('sample_size')->comment('Number of projects in average');
            $table->timestamp('calculated_at')->useCurrent();
            
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');
            
            $table->index(['project_type', 'metric_name'], 'idx_metric_lookup');
            $table->index(['client_id', 'metric_name'], 'idx_client_metric');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_metrics_baseline');
    }
};
```

**Step 2: Create Model**

```bash
php artisan make:model ProjectMetricsBaseline
```

File: `app/Models/ProjectMetricsBaseline.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectMetricsBaseline extends Model
{
    use HasFactory;

    protected $table = 'project_metrics_baseline';

    protected $fillable = [
        'project_type',
        'client_id',
        'metric_name',
        'metric_value',
        'sample_size',
        'calculated_at',
    ];

    protected $casts = [
        'metric_value' => 'decimal:2',
        'sample_size' => 'integer',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get baseline value for a metric
     */
    public static function getBaseline(
        string $metricName, 
        ?string $projectType = null, 
        ?int $clientId = null
    ): ?float {
        $query = self::where('metric_name', $metricName);

        if ($projectType) {
            $query->where('project_type', $projectType);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $result = $query->orderBy('calculated_at', 'desc')->first();

        return $result ? (float) $result->metric_value : null;
    }

    /**
     * Store or update baseline
     */
    public static function storeBaseline(
        string $metricName,
        float $metricValue,
        int $sampleSize,
        ?string $projectType = null,
        ?int $clientId = null
    ): self {
        return self::updateOrCreate(
            [
                'metric_name' => $metricName,
                'project_type' => $projectType,
                'client_id' => $clientId,
            ],
            [
                'metric_value' => $metricValue,
                'sample_size' => $sampleSize,
                'calculated_at' => now(),
            ]
        );
    }
}
```

**Step 3: Create Console Command to Calculate Baselines**

```bash
php artisan make:command CalculateProjectBaselines
```

File: `app/Console/Commands/CalculateProjectBaselines.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectMetricsBaseline;
use Illuminate\Support\Facades\DB;

class CalculateProjectBaselines extends Command
{
    protected $signature = 'projects:calculate-baselines';
    protected $description = 'Calculate historical project baselines for AI analysis';

    public function handle()
    {
        $this->info('Calculating project baselines...');

        // Calculate average project duration
        $this->calculateAverageDuration();

        // Calculate average task count per project
        $this->calculateAverageTaskCount();

        // Calculate average tasks per day (velocity)
        $this->calculateAverageVelocity();

        $this->info('Baselines calculated successfully!');
        
        return 0;
    }

    private function calculateAverageDuration()
    {
        $this->info('- Calculating average project duration...');

        // Get completed projects with both start and end dates
        $avgDuration = Project::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereHas('status', function($query) {
                $query->where('title', 'like', '%complete%');
            })
            ->select(DB::raw('AVG(DATEDIFF(end_date, start_date)) as avg_days'))
            ->first();

        if ($avgDuration && $avgDuration->avg_days) {
            $sampleSize = Project::whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereHas('status', function($query) {
                    $query->where('title', 'like', '%complete%');
                })
                ->count();

            ProjectMetricsBaseline::storeBaseline(
                'avg_duration_days',
                round($avgDuration->avg_days, 2),
                $sampleSize
            );

            $this->info("  ✓ Average duration: {$avgDuration->avg_days} days ({$sampleSize} projects)");
        } else {
            $this->warn('  ⚠ Not enough completed projects to calculate duration baseline');
        }
    }

    private function calculateAverageTaskCount()
    {
        $this->info('- Calculating average task count per project...');

        $avgTasks = Project::withCount('tasks')
            ->whereHas('status', function($query) {
                $query->where('title', 'like', '%complete%');
            })
            ->select(DB::raw('AVG(tasks_count) as avg_tasks'))
            ->first();

        if ($avgTasks && $avgTasks->avg_tasks) {
            $sampleSize = Project::has('tasks')->count();

            ProjectMetricsBaseline::storeBaseline(
                'avg_task_count',
                round($avgTasks->avg_tasks, 2),
                $sampleSize
            );

            $this->info("  ✓ Average task count: {$avgTasks->avg_tasks} tasks ({$sampleSize} projects)");
        }
    }

    private function calculateAverageVelocity()
    {
        $this->info('- Calculating average task velocity...');

        $projects = Project::whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->withCount('tasks')
            ->whereHas('status', function($query) {
                $query->where('title', 'like', '%complete%');
            })
            ->get();

        if ($projects->count() > 0) {
            $totalVelocity = 0;
            $validProjects = 0;

            foreach ($projects as $project) {
                $duration = $project->start_date->diffInDays($project->end_date);
                if ($duration > 0) {
                    $velocity = $project->tasks_count / $duration;
                    $totalVelocity += $velocity;
                    $validProjects++;
                }
            }

            if ($validProjects > 0) {
                $avgVelocity = $totalVelocity / $validProjects;

                ProjectMetricsBaseline::storeBaseline(
                    'avg_tasks_per_day',
                    round($avgVelocity, 2),
                    $validProjects
                );

                $this->info("  ✓ Average velocity: {$avgVelocity} tasks/day ({$validProjects} projects)");
            }
        }
    }
}
```

**Step 4: Run Initial Baseline Calculation**

```bash
# Run on dev/staging first
php artisan projects:calculate-baselines

# Verify results
mysql -u root -p vendorconnect << EOF
SELECT * FROM project_metrics_baseline ORDER BY calculated_at DESC;
EOF
```

**Step 5: Schedule Regular Recalculation**

File: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Recalculate baselines monthly
    $schedule->command('projects:calculate-baselines')->monthly();
    
    // Close stale sessions daily
    $schedule->command('sessions:close-stale')->dailyAt('02:00');
}
```

---

### Day 17-18: Final Testing & Documentation

**Performance Testing:**

```bash
# Test query performance for AI analysis
mysql -u root -p vendorconnect << EOF
-- Test task view queries
EXPLAIN SELECT t.id, t.title, COUNT(tv.id) as view_count
FROM tasks t
LEFT JOIN task_views tv ON t.id = tv.task_id
GROUP BY t.id
LIMIT 100;

-- Test session queries
EXPLAIN SELECT 
    user_id, 
    AVG(duration_seconds) as avg_duration,
    COUNT(*) as session_count
FROM user_sessions
WHERE logout_at IS NOT NULL
GROUP BY user_id
LIMIT 100;

-- Test baseline queries
EXPLAIN SELECT * FROM project_metrics_baseline 
WHERE metric_name = 'avg_duration_days'
ORDER BY calculated_at DESC LIMIT 1;
EOF
```

**Data Validation:**

```bash
# Check data is being collected
mysql -u root -p vendorconnect << EOF
SELECT 'task_views' as table_name, COUNT(*) as row_count FROM task_views
UNION ALL
SELECT 'task_rejections', COUNT(*) FROM task_rejections
UNION ALL
SELECT 'user_sessions', COUNT(*) FROM user_sessions
UNION ALL
SELECT 'project_metrics_baseline', COUNT(*) FROM project_metrics_baseline;
EOF
```

---

### Week 3: Testing Checklist

- [ ] `user_sessions` table created
- [ ] Sessions tracked on login
- [ ] Sessions ended on logout
- [ ] Stale sessions closed by scheduled command
- [ ] `project_metrics_baseline` table created
- [ ] Historical baselines calculated successfully
- [ ] Baseline calculation command runs without errors
- [ ] All indexes created and queries are fast (<100ms)
- [ ] Scheduled tasks configured in Kernel.php

---

## Testing Guide

### Unit Testing

Create test file: `tests/Feature/DataTrackingTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskView;
use App\Models\TaskRejection;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataTrackingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function task_view_is_tracked()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        TaskView::logView($task->id, $user->id);

        $this->assertDatabaseHas('task_views', [
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function task_rejection_is_tracked()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        TaskRejection::logRejection($task->id, $user->id, 'too_busy');

        $this->assertDatabaseHas('task_rejections', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'reason' => 'too_busy',
        ]);
    }

    /** @test */
    public function user_session_is_created_on_login()
    {
        $user = User::factory()->create();
        
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $user->id,
        ]);
    }
}
```

Run tests:

```bash
php artisan test --filter=DataTrackingTest
```

---

## Deployment Guide

### Pre-Deployment Checklist

⚠️ **CRITICAL: Complete ALL items before deploying to production**

- [ ] All code reviewed and approved
- [ ] All tests passing (unit + integration)
- [ ] Database backup completed
- [ ] Rollback plan documented
- [ ] Staging environment tested successfully
- [ ] Performance testing completed
- [ ] No breaking changes to existing functionality
- [ ] All migrations tested on copy of production data
- [ ] Monitoring/alerting configured

---

### Deployment Steps

#### Step 1: Deploy to Staging First

```bash
# SSH into staging server
ssh vc-staging  # or your staging alias

# Navigate to project
cd /path/to/vendorconnect

# Pull latest code
git fetch origin
git checkout feature/phase0-data-tracking
git pull origin feature/phase0-data-tracking

# Install dependencies (if any new)
composer install --no-dev --optimize-autoloader

# Run migrations on staging
php artisan migrate

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run baseline calculation
php artisan projects:calculate-baselines

# Verify tables exist
mysql -u [user] -p vendorconnect << EOF
SHOW TABLES LIKE 'task_views';
SHOW TABLES LIKE 'task_rejections';
SHOW TABLES LIKE 'user_sessions';
SHOW TABLES LIKE 'project_metrics_baseline';
SELECT COUNT(*) FROM task_views;
EOF
```

#### Step 2: Test on Staging

**Test Task View Tracking:**
1. Login to staging frontend
2. Open a task detail page
3. Check network tab for `/track-view` request
4. Verify row in `task_views` table

**Test Session Tracking:**
1. Login to staging
2. Check `user_sessions` table for new row
3. Logout
4. Verify `logout_at` is populated

**Test Performance:**
```bash
# Run load test on staging
ab -n 100 -c 10 https://staging.vc.themastermind.com.au/api/tasks/1
```

**Test Rollback:**
```bash
# Test migration rollback on staging
php artisan migrate:rollback --step=5

# Verify tables removed
mysql -u [user] -p vendorconnect -e "SHOW TABLES;"

# Re-migrate
php artisan migrate
```

---

#### Step 3: Production Deployment

⚠️ **Deploy during low-traffic window (recommended: Sunday 2-4 AM AEST)**

```bash
# 1. SSH into production
ssh vc-server

# 2. Navigate to project
cd /Users/benjaminsimkin/VendorConnect/VendorConnect

# 3. BACKUP PRODUCTION DATABASE FIRST
mysqldump -u root -p vendorconnect > ~/backups/vendorconnect_before_phase0_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh ~/backups/vendorconnect_before_phase0_*.sql

# 4. Pull code (merge feature branch to main first)
git fetch origin
git checkout main
git pull origin main

# 5. Put application in maintenance mode
php artisan down --message="System update in progress. Back online in 10 minutes."

# 6. Install dependencies
composer install --no-dev --optimize-autoloader

# 7. Run migrations
php artisan migrate --force

# Expected output:
# Running migrations.
# 2025_09_30_XXXXXX_create_task_views_table ........................ DONE
# 2025_09_30_XXXXXX_create_task_rejections_table ................... DONE
# 2025_09_30_XXXXXX_add_last_activity_to_task_user_table ........... DONE
# 2025_09_30_XXXXXX_add_indexes_to_ch_messages ..................... DONE
# 2025_09_30_XXXXXX_create_user_sessions_table ..................... DONE
# 2025_09_30_XXXXXX_create_project_metrics_baseline_table .......... DONE

# 8. Clear all caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 9. Calculate initial baselines
php artisan projects:calculate-baselines

# 10. Verify deployment
php artisan migrate:status

# 11. Quick smoke test
curl -I https://vc.themastermind.com.au/api/health
# Should return 200 OK

# 12. Bring application back online
php artisan up

# 13. Verify scheduled tasks are configured
php artisan schedule:list
# Should show:
# - sessions:close-stale .............. Daily at 2:00 AM
# - projects:calculate-baselines ...... Monthly
```

---

#### Step 4: Frontend Deployment

```bash
# If frontend is separate deployment

# SSH to frontend server or build locally
cd /path/to/vendorconnect-frontend

# Pull latest code
git pull origin main

# Install dependencies
npm install

# Build production bundle
npm run build

# Deploy (depends on your setup)
# Option A: Static export
npm run export
# Then copy to server

# Option B: Server deployment
pm2 restart vendorconnect-frontend
```

---

#### Step 5: Post-Deployment Verification

**Immediate Checks (5 minutes):**

```bash
# 1. Verify tables exist and have correct structure
mysql -u root -p vendorconnect << EOF
DESCRIBE task_views;
DESCRIBE task_rejections;
DESCRIBE user_sessions;
DESCRIBE project_metrics_baseline;
DESCRIBE task_user;
EOF

# 2. Check indexes created
mysql -u root -p vendorconnect << EOF
SHOW INDEX FROM task_views;
SHOW INDEX FROM task_rejections;
SHOW INDEX FROM user_sessions;
SHOW INDEX FROM task_user WHERE Column_name = 'last_activity_at';
EOF

# 3. Verify baselines calculated
mysql -u root -p vendorconnect << EOF
SELECT * FROM project_metrics_baseline;
EOF

# 4. Check application logs for errors
tail -f storage/logs/laravel.log
# Should see no errors

# 5. Test login/logout
curl -X POST https://vc.themastermind.com.au/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 6. Verify session tracked
mysql -u root -p vendorconnect << EOF
SELECT * FROM user_sessions ORDER BY id DESC LIMIT 1;
EOF
```

**24-Hour Monitoring:**

```bash
# Check tracking is working
mysql -u root -p vendorconnect << EOF
-- Task views in last 24 hours
SELECT COUNT(*) as views_today FROM task_views 
WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Active sessions
SELECT COUNT(*) as active_sessions FROM user_sessions 
WHERE logout_at IS NULL;

-- Last activity updates
SELECT COUNT(*) as tasks_with_activity FROM task_user 
WHERE last_activity_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
EOF
```

---

## Rollback Procedures

### If Deployment Fails

#### Quick Rollback (Revert Code Only)

```bash
# 1. Put in maintenance mode
php artisan down

# 2. Revert code
git checkout [previous_commit_hash]

# 3. Rollback migrations
php artisan migrate:rollback --step=6

# 4. Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Bring back online
php artisan up
```

#### Full Rollback (Restore Database)

```bash
# 1. Put in maintenance mode
php artisan down

# 2. Restore database from backup
mysql -u root -p vendorconnect < ~/backups/vendorconnect_before_phase0_[timestamp].sql

# 3. Revert code
git checkout [previous_commit_hash]

# 4. Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# 5. Verify restoration
mysql -u root -p vendorconnect << EOF
SHOW TABLES LIKE 'task_views';  -- Should return empty
SHOW TABLES LIKE 'task_rejections';  -- Should return empty
EOF

# 6. Bring back online
php artisan up
```

---

### Partial Rollback (Remove Specific Tables)

If only certain tables are causing issues:

```bash
# Drop problematic table manually
mysql -u root -p vendorconnect << EOF
DROP TABLE IF EXISTS task_views;
DROP TABLE IF EXISTS task_rejections;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS project_metrics_baseline;

-- Revert task_user changes
ALTER TABLE task_user DROP COLUMN last_activity_at;
EOF

# Remove related code temporarily
# Comment out middleware, tracking calls, etc.

# Deploy hotfix
git checkout -b hotfix/disable-tracking
# ... make changes ...
git push origin hotfix/disable-tracking
# ... deploy ...
```

---

## Verification & Monitoring

### Key Metrics to Monitor

**Week 1 Metrics:**
- Task views per day: Target >50 views/day (depends on active users)
- Task view API response time: <100ms
- Database size increase: <1MB/week initially

**Week 2 Metrics:**
- Task rejections per week: Baseline metric (no target)
- Activity timestamp updates: Should match active task interactions
- Middleware overhead: <10ms per request

**Week 3 Metrics:**
- Active sessions: Should match logged-in users
- Session duration averages: Baseline metric
- Baseline calculation: Successfully completes monthly
- Stale session cleanup: Runs daily without errors

---

### Monitoring Queries

**Daily Health Check:**

```sql
-- Run this query daily to monitor tracking health
SELECT 
    'Task Views Today' as metric, 
    COUNT(*) as value 
FROM task_views 
WHERE viewed_at >= CURDATE()

UNION ALL

SELECT 
    'Task Rejections This Week', 
    COUNT(*) 
FROM task_rejections 
WHERE rejected_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

UNION ALL

SELECT 
    'Active Sessions', 
    COUNT(*) 
FROM user_sessions 
WHERE logout_at IS NULL

UNION ALL

SELECT 
    'Sessions This Week', 
    COUNT(*) 
FROM user_sessions 
WHERE login_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

UNION ALL

SELECT 
    'Baselines Calculated', 
    COUNT(*) 
FROM project_metrics_baseline;
```

**Performance Monitoring:**

```sql
-- Check table sizes
SELECT 
    table_name AS "Table",
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = "vendorconnect"
AND table_name IN ('task_views', 'task_rejections', 'user_sessions', 'project_metrics_baseline')
ORDER BY (data_length + index_length) DESC;
```

**Weekly Report:**

```sql
-- Generate weekly summary
SELECT 
    DATE(viewed_at) as date,
    COUNT(*) as views
FROM task_views
WHERE viewed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(viewed_at)
ORDER BY date DESC;
```

---

### Logging & Alerts

**Set up log monitoring:**

```bash
# Watch for tracking errors
tail -f storage/logs/laravel.log | grep -i "tracking\|task_view\|session"

# Set up error alerting (example with sentry or similar)
# Configure in .env:
# SENTRY_LARAVEL_DSN=your_sentry_dsn
```

**Create custom alert script:**

File: `scripts/check_tracking_health.sh`

```bash
#!/bin/bash
# Check if tracking is working

MYSQL_USER="root"
MYSQL_PASS="your_password"
MYSQL_DB="vendorconnect"

# Check views in last hour
VIEWS=$(mysql -u$MYSQL_USER -p$MYSQL_PASS $MYSQL_DB -se \
  "SELECT COUNT(*) FROM task_views WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);")

if [ "$VIEWS" -lt 1 ]; then
  echo "WARNING: No task views in last hour. Tracking may be broken."
  # Send alert email
  echo "No task views tracked in last hour" | mail -s "VendorConnect Tracking Alert" admin@example.com
fi

# Check active sessions
SESSIONS=$(mysql -u$MYSQL_USER -p$MYSQL_PASS $MYSQL_DB -se \
  "SELECT COUNT(*) FROM user_sessions WHERE logout_at IS NULL;")

echo "Health check: $VIEWS views in last hour, $SESSIONS active sessions"
```

**Schedule health check:**

```bash
# Add to crontab
crontab -e

# Run every hour
0 * * * * /path/to/scripts/check_tracking_health.sh
```

---

## Troubleshooting

### Common Issues

#### Issue 1: Migration Fails

**Symptom:**
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solution:**
```bash
# Check which migrations have run
php artisan migrate:status

# If table exists, skip that migration or drop it manually
mysql -u root -p vendorconnect -e "DROP TABLE IF EXISTS task_views;"

# Re-run migrations
php artisan migrate
```

---

#### Issue 2: Task View Tracking Not Working

**Symptoms:**
- No rows in `task_views` table
- Frontend shows no network requests to `/track-view`

**Troubleshooting Steps:**

```bash
# 1. Check if endpoint exists
php artisan route:list | grep track-view

# 2. Check if frontend is making request
# Open browser dev tools > Network tab
# Navigate to task detail page
# Look for POST /api/tasks/{id}/track-view

# 3. Test endpoint manually
curl -X POST https://vc.themastermind.com.au/api/tasks/117/track-view \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# 4. Check Laravel logs
tail -f storage/logs/laravel.log

# 5. Verify table exists and is writable
mysql -u root -p vendorconnect << EOF
DESCRIBE task_views;
INSERT INTO task_views (task_id, user_id, viewed_at) VALUES (1, 1, NOW());
SELECT * FROM task_views WHERE id = LAST_INSERT_ID();
DELETE FROM task_views WHERE id = LAST_INSERT_ID();
EOF
```

---

#### Issue 3: Session Tracking Creating Duplicate Sessions

**Symptom:**
Multiple active sessions for same user

**Solution:**

Update `UserSession::startSession()` to close existing sessions:

```php
public static function startSession(/* ... */): self
{
    // Close any existing active sessions for this user
    self::where('user_id', $userId)
        ->whereNull('logout_at')
        ->each(function($session) {
            $session->endSession();
        });

    // Create new session
    return self::create([/* ... */]);
}
```

---

#### Issue 4: Middleware Slowing Down Requests

**Symptom:**
Requests to `/api/tasks/*` are slower after middleware added

**Solution:**

Make activity tracking async:

```php
// In TrackTaskActivity middleware
use Illuminate\Support\Facades\Queue;

private function updateTaskActivity(Request $request): void
{
    try {
        $taskId = $request->route('id');
        $userId = Auth::id();
        
        // Dispatch to queue instead of synchronous update
        dispatch(function() use ($taskId, $userId) {
            DB::table('task_user')
                ->where('task_id', $taskId)
                ->where('user_id', $userId)
                ->update(['last_activity_at' => now()]);
        })->onQueue('tracking');
        
    } catch (\Exception $e) {
        \Log::debug('Task activity tracking failed', [/* ... */]);
    }
}
```

Configure queue:
```bash
# .env
QUEUE_CONNECTION=redis  # or database

# Run queue worker
php artisan queue:work --queue=tracking
```

---

#### Issue 5: Baseline Calculation Fails

**Symptom:**
`php artisan projects:calculate-baselines` throws error

**Solution:**

```bash
# Check if projects have required data
mysql -u root -p vendorconnect << EOF
-- Projects with dates
SELECT COUNT(*) FROM projects 
WHERE start_date IS NOT NULL AND end_date IS NOT NULL;

-- Projects with tasks
SELECT COUNT(*) FROM projects WHERE id IN (SELECT DISTINCT project_id FROM tasks);
EOF

# If no data, seed some test data or skip baseline calculation
# AI PM can still work with current data, just won't have historical comparisons
```

---

### Performance Issues

#### Slow Queries

If queries are slow, add more indexes:

```sql
-- Additional indexes if needed
ALTER TABLE task_views ADD INDEX idx_task_viewed (task_id, viewed_at);
ALTER TABLE user_sessions ADD INDEX idx_user_login (user_id, login_at);

-- Analyze and optimize tables
ANALYZE TABLE task_views;
ANALYZE TABLE task_rejections;
ANALYZE TABLE user_sessions;

OPTIMIZE TABLE task_views;
OPTIMIZE TABLE task_rejections;
OPTIMIZE TABLE user_sessions;
```

#### Large Table Size

If `task_views` grows too large:

```sql
-- Archive old views (older than 6 months)
CREATE TABLE task_views_archive AS 
SELECT * FROM task_views 
WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

DELETE FROM task_views 
WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

OPTIMIZE TABLE task_views;
```

---

## Success Criteria

### Phase 0 is Complete When:

- [ ] All 4 tracking tables exist in production
- [ ] `task_user.last_activity_at` column added
- [ ] Task views are being logged (>10 per day minimum)
- [ ] Sessions are being tracked on login/logout
- [ ] Historical baselines calculated successfully
- [ ] Scheduled commands configured and running
- [ ] No performance degradation (<50ms overhead max)
- [ ] Rollback tested and documented
- [ ] Monitoring dashboards/queries set up
- [ ] Data collecting for at least 1 week before Phase 1 starts

### Ready for Phase 1 (AI Project Manager) When:

- [ ] Phase 0 running in production for 2+ weeks
- [ ] At least 100 task views logged
- [ ] At least 50 user sessions logged
- [ ] Baselines calculated for at least 10 projects
- [ ] No critical bugs or issues reported
- [ ] Performance metrics within acceptable range
- [ ] Team trained on new tracking features

---

## Additional Resources

### Documentation to Create

1. **User Guide** (if task rejection UI added)
   - How to reject a task
   - What happens when you reject
   - Rejection reasons

2. **Developer Guide**
   - How tracking works
   - How to add new tracking events
   - Database schema diagrams

3. **Admin Guide**
   - How to monitor tracking
   - How to run manual baseline calculations
   - Troubleshooting common issues

### Future Enhancements (Post-Phase 0)

- Track task view duration (how long user spent viewing)
- Track user actions within tasks (deliverable uploads, comments, etc.)
- Add more granular activity types (viewed, edited, commented, etc.)
- Create admin dashboard to view tracking metrics
- Export tracking data for analysis
- More sophisticated baseline calculations (by client, by task type, etc.)

---

## Appendix

### Migration Order

Migrations should run in this order:

1. `create_task_views_table`
2. `create_task_rejections_table`
3. `add_last_activity_to_task_user_table`
4. `add_indexes_to_ch_messages`
5. `create_user_sessions_table`
6. `create_project_metrics_baseline_table`

### Database Backup Commands

```bash
# Full backup
mysqldump -u root -p vendorconnect > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup specific tables only
mysqldump -u root -p vendorconnect \
  task_views task_rejections user_sessions project_metrics_baseline \
  > tracking_tables_backup_$(date +%Y%m%d).sql

# Restore from backup
mysql -u root -p vendorconnect < backup_YYYYMMDD_HHMMSS.sql
```

### Git Commands Reference

```bash
# Create feature branch
git checkout -b feature/phase0-data-tracking

# Commit changes
git add .
git commit -m "Add Phase 0 data tracking infrastructure"

# Push to remote
git push origin feature/phase0-data-tracking

# Merge to main (after review)
git checkout main
git merge feature/phase0-data-tracking
git push origin main

# Tag release
git tag -a phase0-v1.0 -m "Phase 0: Data Collection Infrastructure"
git push origin phase0-v1.0
```

---

## Sign-Off

### Deployment Checklist Sign-Off

- [ ] Code reviewed by: _______________ Date: ___________
- [ ] Staging tested by: _______________ Date: ___________
- [ ] Production deployed by: _______________ Date: ___________
- [ ] Post-deployment verified by: _______________ Date: ___________

---

**END OF GUIDE**

For questions or issues during implementation, contact:
- Technical Lead: Benjamin Simkin
- Project: VendorConnect AI Project Manager
- Phase: Phase 0 - Data Collection Infrastructure




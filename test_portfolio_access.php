<?php

require_once 'vendor/autoload.php';

use App\Models\Task;
use App\Models\User;
use App\Models\Portfolio;
use App\Models\TaskAssignmentHistory;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Portfolio Access for Historical Task Assignments ===\n\n";

// Find a tasker user
$tasker = User::whereHas('roles', function($q) {
    $q->where('name', 'Tasker');
})->first();

if (!$tasker) {
    echo "No tasker user found\n";
    exit(1);
}

echo "Using tasker: " . $tasker->email . " (ID: " . $tasker->id . ")\n\n";

// Check current task assignments
$currentTasks = $tasker->tasks()->with(['project.clients'])->get();
echo "Current task assignments: " . $currentTasks->count() . "\n";
foreach ($currentTasks as $task) {
    echo "- Task ID: " . $task->id . " | Title: " . $task->title . "\n";
    if ($task->project && $task->project->clients->count() > 0) {
        foreach ($task->project->clients as $client) {
            echo "  - Client: " . $client->name . " (ID: " . $client->id . ")\n";
        }
    }
}

// Check historical assignments
$historicalAssignments = TaskAssignmentHistory::where('user_id', $tasker->id)
    ->where('action', 'assigned')
    ->with(['client', 'task'])
    ->get();

echo "\nHistorical assignments: " . $historicalAssignments->count() . "\n";
foreach ($historicalAssignments as $assignment) {
    echo "- Task ID: " . $assignment->task_id . " | Client: " . $assignment->client->name . " | Date: " . $assignment->action_date . "\n";
}

// Check portfolio access
$accessiblePortfolios = Portfolio::whereHas('task', function($q) use ($tasker) {
    $q->whereHas('users', function($subQ) use ($tasker) {
        $subQ->where('users.id', $tasker->id);
    });
})->orWhereHas('task', function($q) use ($tasker) {
    $q->whereHas('project.clients', function($subQ) use ($tasker) {
        $subQ->whereHas('taskAssignmentHistory', function($subSubQ) use ($tasker) {
            $subSubQ->where('user_id', $tasker->id)
                  ->where('action', 'assigned');
        });
    });
})->with(['client', 'task'])->get();

echo "\nAccessible portfolio items: " . $accessiblePortfolios->count() . "\n";
foreach ($accessiblePortfolios as $portfolio) {
    echo "- Portfolio ID: " . $portfolio->id . " | Title: " . $portfolio->title . " | Client: " . $portfolio->client->name . "\n";
}

echo "\n=== Test Complete ===\n";

<?php

require_once 'vendor/autoload.php';

use App\Models\Task;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating existing repeating task titles...\n";

// Get all child tasks (repeating instances) that don't have dates in their titles
$childTasks = Task::whereNotNull('parent_task_id')
    ->where('title', 'NOT LIKE', '% % %') // Doesn't contain date pattern
    ->get();

$updatedCount = 0;

foreach ($childTasks as $task) {
    $startDate = Carbon::parse($task->start_date);
    $formattedDate = $startDate->format('j M Y');
    
    // Extract the original title (remove any existing date)
    $originalTitle = preg_replace('/\s+\d{1,2}\s+[A-Za-z]{3}\s+\d{4}$/', '', $task->title);
    
    // Create new title with date
    $newTitle = $originalTitle . ' ' . $formattedDate;
    
    if ($newTitle !== $task->title) {
        $task->update(['title' => $newTitle]);
        echo "Updated task {$task->id}: '{$task->title}' -> '{$newTitle}'\n";
        $updatedCount++;
    }
}

echo "Updated {$updatedCount} existing repeating task titles.\n";

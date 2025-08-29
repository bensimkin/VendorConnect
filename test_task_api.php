<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Task;
use App\Models\User;

echo "Testing Task API response...\n";

// Simulate the API call
$user = User::find(42); // Admin user
auth()->login($user);

$task = Task::with(['users', 'status', 'priority', 'taskType', 'template', 'project', 'project.clients', 'questionAnswers.briefQuestions', 'checklistAnswers', 'deliverables.creator', 'deliverables.media', 'messages.sender'])
    ->find(72);

if ($task) {
    echo "Task: {$task->title}\n";
    echo "Project: " . ($task->project ? $task->project->title : 'No project') . "\n";
    
    if ($task->project && $task->project->clients) {
        echo "Project clients: " . $task->project->clients->count() . "\n";
        foreach ($task->project->clients as $client) {
            echo "  - {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
        }
    } else {
        echo "No project clients found\n";
    }
    
    // Add clients from project to task response
    if ($task->project && $task->project->clients) {
        $task->clients = $task->project->clients;
        echo "Added clients to task response: " . $task->clients->count() . "\n";
    } else {
        $task->clients = collect();
        echo "No clients to add\n";
    }
    
    // Check if clients are now in the task
    echo "Task clients: " . ($task->clients ? $task->clients->count() : 0) . "\n";
    
} else {
    echo "Task not found\n";
}

echo "\nDone!\n";
?>

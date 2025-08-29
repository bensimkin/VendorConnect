<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\Client;
use App\Models\Task;

echo "Testing client relationships...\n";

// Test 1: Check if clients exist
$clients = Client::all();
echo "Found " . $clients->count() . " clients:\n";
foreach ($clients as $client) {
    echo "- {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
}

// Test 2: Check if projects exist
$projects = Project::all();
echo "\nFound " . $projects->count() . " projects:\n";
foreach ($projects as $project) {
    echo "- {$project->title} (ID: {$project->id})\n";
}

// Test 3: Check client_project relationships
echo "\nChecking client_project relationships:\n";
foreach ($projects as $project) {
    $projectClients = $project->clients;
    echo "Project '{$project->title}' has " . $projectClients->count() . " clients:\n";
    foreach ($projectClients as $client) {
        echo "  - {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
    }
}

// Test 4: Check task with project and clients
echo "\nTesting task with project and clients:\n";
$task = Task::with(['project', 'project.clients'])->find(72);
if ($task) {
    echo "Task: {$task->title}\n";
    if ($task->project) {
        echo "Project: {$task->project->title}\n";
        echo "Project clients: " . $task->project->clients->count() . "\n";
        foreach ($task->project->clients as $client) {
            echo "  - {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
        }
    } else {
        echo "No project found\n";
    }
} else {
    echo "Task not found\n";
}

echo "\nDone!\n";
?>

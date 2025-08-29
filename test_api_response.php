<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\User;

echo "Testing API response...\n";

// Simulate the API call
$user = User::find(42); // Admin user
auth()->login($user);

$project = Project::with(['users', 'tasks.status', 'status', 'clients'])
    ->find(8);

if ($project) {
    echo "Project: {$project->title}\n";
    echo "Has clients relationship: " . (isset($project->clients) ? 'Yes' : 'No') . "\n";
    echo "Clients count: " . ($project->clients ? $project->clients->count() : 0) . "\n";
    
    if ($project->clients) {
        foreach ($project->clients as $client) {
            echo "- {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
        }
    }
    
    // Convert to array to see what would be serialized
    $projectArray = $project->toArray();
    echo "\nProject array keys: " . implode(', ', array_keys($projectArray)) . "\n";
    
    if (isset($projectArray['clients'])) {
        echo "Clients in array: " . count($projectArray['clients']) . "\n";
    } else {
        echo "No clients in array\n";
    }
    
} else {
    echo "Project not found\n";
}

echo "\nDone!\n";
?>

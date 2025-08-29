<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\Client;

echo "Testing project-client relationships...\n";

// Test 1: Check if client_project table has data
$clientProjects = \Illuminate\Support\Facades\DB::table('client_project')->get();
echo "Found " . $clientProjects->count() . " client_project records:\n";
foreach ($clientProjects as $cp) {
    echo "- Project ID: {$cp->project_id}, Client ID: {$cp->client_id}\n";
}

// Test 2: Check project with clients relationship
$project = Project::with('clients')->find(8);
if ($project) {
    echo "\nProject: {$project->title}\n";
    echo "Clients count: " . $project->clients->count() . "\n";
    foreach ($project->clients as $client) {
        echo "- {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
    }
} else {
    echo "Project not found\n";
}

// Test 3: Check raw query
echo "\nTesting raw query:\n";
$clients = \Illuminate\Support\Facades\DB::table('clients')
    ->join('client_project', 'clients.id', '=', 'client_project.client_id')
    ->where('client_project.project_id', 8)
    ->get();
echo "Raw query found " . $clients->count() . " clients for project 8:\n";
foreach ($clients as $client) {
    echo "- {$client->first_name} {$client->last_name} (ID: {$client->id})\n";
}

echo "\nDone!\n";
?>

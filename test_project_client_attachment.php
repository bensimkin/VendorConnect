<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

echo "Testing project-client attachment...\n";

// Test 1: Check if project 11 exists
$project = Project::find(11);
if (!$project) {
    echo "Project 11 not found!\n";
    exit;
}
echo "Project 11 found: {$project->title}\n";

// Test 2: Check if client 100 exists
$client = Client::find(100);
if (!$client) {
    echo "Client 100 not found!\n";
    exit;
}
echo "Client 100 found: {$client->first_name} {$client->last_name}\n";

// Test 3: Check current client_project records
$clientProjects = DB::table('client_project')->where('project_id', 11)->get();
echo "Current client_project records for project 11: " . $clientProjects->count() . "\n";
foreach ($clientProjects as $cp) {
    echo "- Client ID: {$cp->client_id}, Admin ID: {$cp->admin_id}\n";
}

// Test 4: Try to attach client manually
echo "\nAttempting to attach client 100 to project 11...\n";
try {
    $project->clients()->attach(100, ['admin_id' => 42]);
    echo "Client attachment successful!\n";
} catch (Exception $e) {
    echo "Client attachment failed: " . $e->getMessage() . "\n";
}

// Test 5: Check if attachment worked
$clientProjects = DB::table('client_project')->where('project_id', 11)->get();
echo "After attachment - client_project records for project 11: " . $clientProjects->count() . "\n";
foreach ($clientProjects as $cp) {
    echo "- Client ID: {$cp->client_id}, Admin ID: {$cp->admin_id}\n";
}

// Test 6: Check if relationship loads
$project->load('clients');
echo "Project clients count: " . $project->clients->count() . "\n";
foreach ($project->clients as $client) {
    echo "- Client: {$client->first_name} {$client->last_name}\n";
}

echo "Done!\n";
?>

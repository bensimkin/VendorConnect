<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

echo "Assigning clients to projects...\n";

// Get all projects and clients
$projects = Project::all();
$clients = Client::all();

if ($clients->isEmpty()) {
    echo "No clients found!\n";
    exit;
}

foreach ($projects as $project) {
    // Check if project already has clients
    $existingClients = DB::table('client_project')
        ->where('project_id', $project->id)
        ->get();
    
    if ($existingClients->isEmpty()) {
        // Assign the first client to this project
        $client = $clients->first();
        DB::table('client_project')->insert([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'admin_id' => 1,
        ]);
        echo "Assigned client '{$client->first_name} {$client->last_name}' to project '{$project->title}'\n";
    } else {
        echo "Project '{$project->title}' already has clients assigned\n";
    }
}

echo "Done!\n";
?>

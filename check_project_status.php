<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\Status;

echo "Checking project status relationships:\n";
$projects = Project::with('status')->get();
foreach($projects as $project) {
    echo "Project ID: " . $project->id . ", Status ID: " . $project->status_id . ", Status: " . ($project->status ? $project->status->title : 'NULL') . "\n";
}

echo "\nChecking available statuses:\n";
$statuses = Status::all();
foreach($statuses as $status) {
    echo "Status ID: " . $status->id . ", Title: " . $status->title . "\n";
}
?>

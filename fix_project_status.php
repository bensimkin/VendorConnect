<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\Status;

echo "Fixing project status IDs...\n";

// Get the first available status (Active - ID 20)
$activeStatus = Status::where('title', 'Active')->first();
if (!$activeStatus) {
    echo "Active status not found!\n";
    exit;
}

// Update all projects with invalid status_id to use Active status
$updated = Project::where('status_id', 1)->update(['status_id' => $activeStatus->id]);
echo "Updated $updated projects to use status_id: " . $activeStatus->id . " (Active)\n";

// Verify the fix
echo "\nVerifying fix:\n";
$projects = Project::with('status')->get();
foreach($projects as $project) {
    echo "Project ID: " . $project->id . ", Status ID: " . $project->status_id . ", Status: " . ($project->status ? $project->status->title : 'NULL') . "\n";
}
?>

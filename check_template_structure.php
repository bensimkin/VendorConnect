<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\TaskBriefTemplates;

echo "Template table columns:\n";
$columns = Schema::getColumnListing('task_brief_templates');
foreach($columns as $col) {
    echo "- $col\n";
}

echo "\nSample template data:\n";
$template = TaskBriefTemplates::first();
if ($template) {
    echo "Template ID: " . $template->id . "\n";
    echo "Template data: " . json_encode($template->toArray(), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No templates found\n";
}

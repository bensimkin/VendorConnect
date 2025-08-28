<?php
require __DIR__.'/vendor/autoload.php';

use App\Models\TaskBriefTemplates;
use App\Models\Task;

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking template usage...\n\n";

// Find the templates by name
$templateNames = ['Design website page (Copy)', 'Graphics Template'];

foreach ($templateNames as $templateName) {
    echo "=== Checking: $templateName ===\n";
    
    $template = TaskBriefTemplates::where('title', $templateName)->first();
    
    if (!$template) {
        echo "Template not found\n\n";
        continue;
    }
    
    echo "Template ID: {$template->id}\n";
    
    // Check if any tasks are using this template
    $tasksUsingTemplate = Task::where('template_id', $template->id)->get();
    
    if ($tasksUsingTemplate->count() > 0) {
        echo "Found {$tasksUsingTemplate->count()} tasks using this template:\n";
        foreach ($tasksUsingTemplate as $task) {
            echo "  - Task #{$task->id}: {$task->title} (Created: {$task->created_at})\n";
        }
        echo "\n";
        
        // Option to nullify the template_id for these tasks
        echo "To delete this template, we need to remove the template reference from these tasks.\n";
    } else {
        echo "No tasks are using this template.\n";
        echo "Attempting to delete the template...\n";
        try {
            $template->delete();
            echo "Template deleted successfully!\n";
        } catch (\Exception $e) {
            echo "Error deleting template: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Also check for templates that might have constraint issues
echo "=== Checking all templates with task references ===\n";
$templatesWithTasks = TaskBriefTemplates::whereHas('tasks')->withCount('tasks')->get();

if ($templatesWithTasks->count() > 0) {
    echo "Templates currently in use:\n";
    foreach ($templatesWithTasks as $template) {
        echo "  - {$template->title} (ID: {$template->id}) - Used by {$template->tasks_count} tasks\n";
    }
} else {
    echo "No templates are currently being used by tasks.\n";
}

echo "\nDone!\n";



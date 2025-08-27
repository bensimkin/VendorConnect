<?php
require __DIR__.'/vendor/autoload.php';

use App\Models\TaskBriefTemplates;
use App\Models\Task;
use App\Models\TaskBriefQuestion;
use App\Models\TaskBriefChecklist;

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing template deletion issues...\n\n";

// Templates that cannot be deleted
$problemTemplates = ['Design website page (Copy)', 'Graphics Template'];

foreach ($problemTemplates as $templateName) {
    echo "=== Processing: $templateName ===\n";
    
    $template = TaskBriefTemplates::where('title', $templateName)->first();
    
    if (!$template) {
        echo "Template not found\n\n";
        continue;
    }
    
    echo "Template ID: {$template->id}\n";
    
    // Check if any tasks are using this template
    $tasksUsingTemplate = Task::where('template_id', $template->id)->get();
    
    if ($tasksUsingTemplate->count() > 0) {
        echo "Found {$tasksUsingTemplate->count()} tasks using this template\n";
        echo "Removing template reference from these tasks...\n";
        
        // Remove the template reference from all tasks
        $updated = Task::where('template_id', $template->id)->update(['template_id' => null]);
        echo "Updated $updated tasks\n";
    }
    
    // Delete related brief questions
    $questionsCount = TaskBriefQuestion::where('task_brief_templates_id', $template->id)->count();
    if ($questionsCount > 0) {
        echo "Deleting $questionsCount brief questions...\n";
        TaskBriefQuestion::where('task_brief_templates_id', $template->id)->delete();
    }
    
    // Delete related brief checklists
    $checklistsCount = TaskBriefChecklist::where('task_brief_templates_id', $template->id)->count();
    if ($checklistsCount > 0) {
        echo "Deleting $checklistsCount brief checklists...\n";
        TaskBriefChecklist::where('task_brief_templates_id', $template->id)->delete();
    }
    
    // Now try to delete the template
    echo "Attempting to delete the template...\n";
    try {
        $template->delete();
        echo "✓ Template '$templateName' deleted successfully!\n";
    } catch (\Exception $e) {
        echo "✗ Error deleting template: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "Done!\n";

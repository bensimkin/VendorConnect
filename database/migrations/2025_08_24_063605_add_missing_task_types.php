<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing task types to match the templates
        $taskTypes = [
            ['task_type' => 'Website Design'],
            ['task_type' => 'Development'],
            ['task_type' => 'Marketing'],
            ['task_type' => 'Content Creation'],
            ['task_type' => 'SEO'],
        ];
        
        foreach ($taskTypes as $taskType) {
            // Check if task type already exists
            $exists = DB::table('task_types')->where('task_type', $taskType['task_type'])->exists();
            if (!$exists) {
                DB::table('task_types')->insert($taskType);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the added task types
        $taskTypes = [
            'Website Design',
            'Development', 
            'Marketing',
            'Content Creation',
            'SEO',
        ];
        
        DB::table('task_types')->whereIn('task_type', $taskTypes)->delete();
    }
};

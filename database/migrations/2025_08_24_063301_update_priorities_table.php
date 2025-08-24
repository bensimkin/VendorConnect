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
        // Clear existing priorities
        DB::table('priorities')->truncate();
        
        // Insert the new priority values
        $priorities = [
            ['title' => 'Low', 'slug' => 'low', 'admin_id' => 1],
            ['title' => 'Medium', 'slug' => 'medium', 'admin_id' => 1],
            ['title' => 'High', 'slug' => 'high', 'admin_id' => 1],
            ['title' => 'Urgent', 'slug' => 'urgent', 'admin_id' => 1],
            ['title' => 'Not Urgent', 'slug' => 'not-urgent', 'admin_id' => 1],
        ];
        
        DB::table('priorities')->insert($priorities);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the priorities table
        DB::table('priorities')->truncate();
    }
};

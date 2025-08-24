<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProjectStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add project-specific statuses to the existing statuses table
        DB::table('statuses')->insert([
            [
                'title' => 'Active',
                'slug' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Inactive',
                'slug' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Completed',
                'slug' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the project statuses we added
        DB::table('statuses')->whereIn('slug', ['active', 'inactive'])->delete();
        
        // Note: We don't delete 'completed' as it might already exist
    }
}

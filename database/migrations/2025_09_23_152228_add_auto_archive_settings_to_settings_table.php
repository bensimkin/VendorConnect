<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add auto-archive settings to the settings table
        DB::table('settings')->insert([
            [
                'key' => 'auto_archive_enabled',
                'value' => '0',
                'group' => 'archive_settings',
                'description' => 'Enable automatic archiving of completed tasks',
                'type' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_archive_days',
                'value' => '30',
                'group' => 'archive_settings',
                'description' => 'Number of days after completion to auto-archive tasks',
                'type' => 'integer',
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
        // Remove the auto-archive settings
        DB::table('settings')->whereIn('key', ['auto_archive_enabled', 'auto_archive_days'])->delete();
    }
};
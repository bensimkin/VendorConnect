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
        // Insert default project settings
        $settings = [
            [
                'key' => 'allow_multiple_clients_per_project',
                'value' => '0', // Default to false
                'type' => 'boolean',
                'group' => 'project',
                'description' => 'Allow multiple clients to be assigned to a single project'
            ],
            [
                'key' => 'require_project_client',
                'value' => '1', // Default to true
                'type' => 'boolean',
                'group' => 'project',
                'description' => 'Require at least one client to be assigned to a project'
            ],
            [
                'key' => 'max_clients_per_project',
                'value' => '5', // Default to 5
                'type' => 'integer',
                'group' => 'project',
                'description' => 'Maximum number of clients that can be assigned to a single project'
            ]
        ];

        foreach ($settings as $setting) {
            // Check if setting already exists
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->whereIn('key', [
            'allow_multiple_clients_per_project',
            'require_project_client',
            'max_clients_per_project'
        ])->delete();
    }
};

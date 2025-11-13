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
        // Get the first admin ID (should be 1 for existing installation)
        $adminId = DB::table('admins')->orderBy('id')->value('id');
        
        if (!$adminId) {
            // If no admin exists, create one for the first user with admin role
            $adminUser = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'admin')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->select('users.id')
                ->first();
            
            if ($adminUser) {
                $adminId = DB::table('admins')->insertGetId([
                    'user_id' => $adminUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Fallback: create admin for first user
                $firstUser = DB::table('users')->orderBy('id')->first();
                if ($firstUser) {
                    $adminId = DB::table('admins')->insertGetId([
                        'user_id' => $firstUser->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // Populate NULL admin_id values in existing tables
        $tablesToUpdate = [
            'projects',
            'tasks',
            'clients',
            'workspaces',
            'statuses',
            'priorities',
            'tags',
            'notes',
            'activity_logs',
            'team_members',
        ];
        
        foreach ($tablesToUpdate as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'admin_id')) {
                DB::table($table)->whereNull('admin_id')->update(['admin_id' => $adminId]);
            }
        }
        
        // Populate NULL admin_id values in newly added admin_id columns
        $newTablesToUpdate = [
            'templates',
            'task_types',
            'task_brief_templates',
            'task_brief_questions',
            'expenses',
            'expense_types',
            'portfolios',
            'api_keys',
            'items',
            'milestones',
        ];
        
        foreach ($newTablesToUpdate as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'admin_id')) {
                DB::table($table)->whereNull('admin_id')->update(['admin_id' => $adminId]);
            }
        }
        
        // Update pivot tables with admin_id
        $pivotTables = [
            'client_project',
            'client_workspace',
            'project_tag',
            'project_user',
            'user_workspace',
        ];
        
        foreach ($pivotTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'admin_id')) {
                DB::table($table)->whereNull('admin_id')->update(['admin_id' => $adminId]);
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
        // No need to reverse - data population is safe to keep
    }
};


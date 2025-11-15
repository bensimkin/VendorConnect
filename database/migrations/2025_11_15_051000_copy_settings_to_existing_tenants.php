<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all companies (admins)
        $admins = Admin::all();
        
        // Default settings template
        $defaultSettings = [
            // Archive settings
            [
                'key' => 'auto_archive_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'archive_settings',
                'description' => 'Enable automatic archiving of completed tasks'
            ],
            [
                'key' => 'auto_archive_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'archive_settings',
                'description' => 'Number of days after completion to auto-archive tasks'
            ],
            // Project settings
            [
                'key' => 'allow_multiple_clients_per_project',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'project',
                'description' => 'Allow multiple clients to be assigned to a single project'
            ],
            [
                'key' => 'require_project_client',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'project',
                'description' => 'Require at least one client to be assigned to a project'
            ],
            [
                'key' => 'max_clients_per_project',
                'value' => '5',
                'type' => 'integer',
                'group' => 'project',
                'description' => 'Maximum number of clients that can be assigned to a single project'
            ],
        ];

        foreach ($admins as $admin) {
            foreach ($defaultSettings as $settingTemplate) {
                // Check if this setting already exists for this company
                $exists = Setting::where('admin_id', $admin->id)
                    ->where('key', $settingTemplate['key'])
                    ->exists();
                
                if (!$exists) {
                    // Create the setting for this company
                    Setting::create(array_merge($settingTemplate, ['admin_id' => $admin->id]));
                }
            }
            
            // Create general_settings if doesn't exist
            $generalSettingsExists = Setting::where('admin_id', $admin->id)
                ->where('key', 'general_settings')
                ->exists();
            
            if (!$generalSettingsExists) {
                Setting::create([
                    'admin_id' => $admin->id,
                    'key' => 'general_settings',
                    'value' => json_encode([
                        'company_title' => $admin->company_name ?: 'VendorConnect',
                        'timezone' => 'Australia/Brisbane',
                        'date_format' => 'DD-MM-YYYY|d-m-Y',
                        'currency_code' => 'AUD',
                        'currency_symbol' => '$',
                        'currency_symbol_position' => 'before',
                        'decimal_points_in_currency' => '2'
                    ]),
                    'type' => 'json',
                    'group' => 'general',
                    'description' => 'General company settings'
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration copies data, rollback would delete all tenant-specific settings
        // Not recommended to rollback
    }
};


<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking admin user permissions...\n";

// Find the admin user
$adminUser = User::find(42);
if (!$adminUser) {
    echo "Admin user (ID: 42) not found!\n";
    exit(1);
}

echo "Found admin user: {$adminUser->email}\n";

// Check if admin role exists
$adminRole = Role::where('name', 'admin')->first();
if (!$adminRole) {
    echo "Creating admin role...\n";
    $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    echo "Admin role created with ID: {$adminRole->id}\n";
} else {
    echo "Admin role exists with ID: {$adminRole->id}\n";
}

// Check current roles
$currentRoles = $adminUser->roles;
echo "Current roles: ";
if ($currentRoles->count() > 0) {
    foreach ($currentRoles as $role) {
        echo $role->name . " ";
    }
} else {
    echo "No roles assigned";
}
echo "\n";

// Assign admin role if not already assigned
if (!$adminUser->hasRole('admin')) {
    echo "Assigning admin role to user...\n";
    $adminUser->assignRole('admin');
    echo "Admin role assigned successfully!\n";
} else {
    echo "Admin role already assigned.\n";
}

// Check permissions
$permissions = $adminUser->getAllPermissions();
echo "Current permissions: ";
if ($permissions->count() > 0) {
    foreach ($permissions as $permission) {
        echo $permission->name . " ";
    }
} else {
    echo "No permissions found";
}
echo "\n";

// Create basic permissions if they don't exist
$basicPermissions = [
    'create_projects', 'manage_projects', 'edit_projects', 'delete_projects',
    'create_tasks', 'manage_tasks', 'edit_tasks', 'delete_tasks',
    'create_users', 'manage_users', 'edit_users', 'delete_users',
    'create_clients', 'manage_clients', 'edit_clients', 'delete_clients',
    'create_workspaces', 'manage_workspaces', 'edit_workspaces', 'delete_workspaces',
    'create_meetings', 'manage_meetings', 'edit_meetings', 'delete_meetings',
    'create_contracts', 'manage_contracts', 'edit_contracts', 'delete_contracts',
    'create_timesheet', 'manage_timesheet', 'delete_timesheet',
    'create_payslips', 'manage_payslips', 'edit_payslips', 'delete_payslips',
    'manage_activity_log', 'delete_activity_log',
    'create_estimates_invoices', 'manage_estimates_invoices', 'edit_estimates_invoices', 'delete_estimates_invoices',
    'create_expenses', 'manage_expenses', 'edit_expenses', 'delete_expenses',
    'create_milestones', 'manage_milestones', 'edit_milestones', 'delete_milestones',
    'manage_system_notifications', 'delete_system_notifications'
];

echo "Creating basic permissions...\n";
foreach ($basicPermissions as $permissionName) {
    $permission = Permission::where('name', $permissionName)->first();
    if (!$permission) {
        Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
        echo "Created permission: {$permissionName}\n";
    }
}

// Give admin role all permissions
echo "Giving admin role all permissions...\n";
$adminRole->syncPermissions(Permission::all());
echo "Admin role now has all permissions.\n";

// Clear permission cache
echo "Clearing permission cache...\n";
$adminUser->forgetCachedPermissions();
$adminRole->forgetCachedPermissions();

echo "Done! Admin user should now have full permissions.\n";

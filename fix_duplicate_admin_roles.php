<?php
require __DIR__.'/vendor/autoload.php';

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing duplicate Admin roles...\n\n";

// First, let's see what roles exist
echo "Current roles in database:\n";
$allRoles = Role::all();
foreach ($allRoles as $role) {
    echo "ID: {$role->id}, Name: '{$role->name}', Guard: '{$role->guard_name}'\n";
}
echo "\n";

// We want to keep only one 'admin' role for the 'api' guard
$correctAdminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
if (!$correctAdminRole) {
    // If no lowercase 'admin' exists for api guard, convert one of the existing Admin roles
    $existingAdmin = Role::where('name', 'Admin')->where('guard_name', 'api')->first();
    if ($existingAdmin) {
        echo "Converting existing 'Admin' (ID: {$existingAdmin->id}) to lowercase 'admin' for api guard...\n";
        $existingAdmin->name = 'admin';
        $existingAdmin->save();
        $correctAdminRole = $existingAdmin;
    } else {
        echo "Creating new 'admin' role for 'api' guard...\n";
        $correctAdminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
    }
} else {
    echo "Found correct 'admin' role (ID: {$correctAdminRole->id}, guard: api)\n";
}

// Sync all permissions to the correct admin role
$allPermissions = Permission::where('guard_name', 'api')->get();
$correctAdminRole->syncPermissions($allPermissions);
echo "Synced " . $allPermissions->count() . " permissions to admin role\n\n";

// Find all users with any admin role variation
$adminUsers = User::whereHas('roles', function($query) {
    $query->whereIn('name', ['Admin', 'admin', 'ADMIN']);
})->get();

echo "Found " . $adminUsers->count() . " admin users to update\n";

// Update each admin user
foreach ($adminUsers as $user) {
    echo "\nProcessing user: {$user->email} (ID: {$user->id})\n";
    
    // Get current roles
    $currentRoles = $user->roles->pluck('name')->toArray();
    echo "Current roles: " . implode(', ', $currentRoles) . "\n";
    
    // Detach ALL roles first
    $user->roles()->detach();
    
    // Then assign only the correct admin role
    $user->assignRole($correctAdminRole);
    
    echo "Updated to single 'admin' role\n";
}

// Now delete all other Admin role variations
echo "\nDeleting duplicate admin roles...\n";
$rolesToDelete = Role::whereIn('name', ['Admin', 'admin', 'ADMIN'])
    ->where('id', '!=', $correctAdminRole->id)
    ->get();

foreach ($rolesToDelete as $role) {
    echo "Deleting role: '{$role->name}' (guard: {$role->guard_name}, ID: {$role->id})\n";
    
    // First detach from all users (in case any were missed)
    \DB::table('model_has_roles')
        ->where('role_id', $role->id)
        ->delete();
    
    // Delete role permissions
    \DB::table('role_has_permissions')
        ->where('role_id', $role->id)
        ->delete();
    
    // Delete the role
    $role->delete();
}

// Clear permission cache
app(PermissionRegistrar::class)->forgetCachedPermissions();

echo "\nVerifying results...\n";
$remainingRoles = Role::all();
echo "All roles in database after cleanup:\n";
foreach ($remainingRoles as $role) {
    echo "  - '{$role->name}' (guard: {$role->guard_name}, ID: {$role->id})\n";
}

// Check admin users
echo "\nAdmin users verification:\n";
foreach ($adminUsers as $user) {
    $user->refresh();
    echo "User {$user->email}: " . implode(', ', $user->roles->pluck('name')->toArray()) . "\n";
}

echo "\nDone! All admin users now have a single lowercase 'admin' role.\n";



<?php
require __DIR__.'/vendor/autoload.php';

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing Admin roles...\n\n";

// First, let's see what roles exist
echo "Current roles in database:\n";
$allRoles = Role::all();
foreach ($allRoles as $role) {
    echo "ID: {$role->id}, Name: '{$role->name}', Guard: '{$role->guard_name}'\n";
}
echo "\n";

// Find all variations of admin role
$adminRoles = Role::whereIn('name', ['Admin', 'admin', 'ADMIN'])->get();
echo "Found " . $adminRoles->count() . " admin role variations\n\n";

// Find or create the correct lowercase 'admin' role for 'api' guard (since we're using API)
$correctAdminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
if (!$correctAdminRole) {
    echo "Creating correct 'admin' role for 'api' guard...\n";
    $correctAdminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
} else {
    echo "Found correct 'admin' role (ID: {$correctAdminRole->id})\n";
}

// Sync all permissions to the correct admin role
$allPermissions = Permission::where('guard_name', 'api')->get();
$correctAdminRole->syncPermissions($allPermissions);
echo "Synced " . $allPermissions->count() . " permissions to admin role\n";

// Find all users with any admin role variation
$adminUsers = User::whereHas('roles', function($query) {
    $query->whereIn('name', ['Admin', 'admin', 'ADMIN']);
})->get();

echo "\nFound " . $adminUsers->count() . " admin users\n";

// Update each admin user to have only the correct admin role
foreach ($adminUsers as $user) {
    echo "\nProcessing user: {$user->email} (ID: {$user->id})\n";
    
    // Get current roles
    $currentRoles = $user->roles->pluck('name')->toArray();
    echo "Current roles: " . implode(', ', $currentRoles) . "\n";
    
    // Remove all admin role variations
    $user->removeRole('Admin');
    $user->removeRole('admin');
    $user->removeRole('ADMIN');
    
    // Assign the correct admin role
    $user->assignRole($correctAdminRole);
    
    echo "Updated to single 'admin' role\n";
}

// Clean up duplicate/incorrect admin roles
echo "\nCleaning up duplicate admin roles...\n";
foreach ($adminRoles as $role) {
    if ($role->id !== $correctAdminRole->id) {
        // First unassign from all users
        $usersWithRole = User::role($role)->get();
        foreach ($usersWithRole as $user) {
            $user->removeRole($role);
        }
        
        // Then delete the role
        echo "Deleting duplicate role: '{$role->name}' (guard: {$role->guard_name}, ID: {$role->id})\n";
        $role->delete();
    }
}

// Also check for 'web' guard admin roles and remove them
$webAdminRoles = Role::where('guard_name', 'web')->whereIn('name', ['Admin', 'admin'])->get();
foreach ($webAdminRoles as $role) {
    echo "Deleting web guard role: '{$role->name}' (ID: {$role->id})\n";
    // First unassign from all users
    $usersWithRole = User::role($role)->get();
    foreach ($usersWithRole as $user) {
        $user->removeRole($role);
    }
    $role->delete();
}

// Clear permission cache
app(PermissionRegistrar::class)->forgetCachedPermissions();

echo "\nVerifying results...\n";
$remainingRoles = Role::whereIn('name', ['Admin', 'admin', 'ADMIN'])->get();
echo "Admin roles remaining: " . $remainingRoles->count() . "\n";
foreach ($remainingRoles as $role) {
    echo "  - '{$role->name}' (guard: {$role->guard_name})\n";
}

// Check admin user
$adminUser = User::find(42);
if ($adminUser) {
    echo "\nAdmin user {$adminUser->email} roles: " . implode(', ', $adminUser->roles->pluck('name')->toArray()) . "\n";
}

echo "\nDone! All admin users now have a single lowercase 'admin' role.\n";

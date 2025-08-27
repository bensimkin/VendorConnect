<?php
require __DIR__.'/vendor/autoload.php';

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Renaming Admin role to lowercase...\n\n";

// Find the Admin role with API guard
$adminRole = Role::where('name', 'Admin')->where('guard_name', 'api')->first();

if ($adminRole) {
    echo "Found 'Admin' role (ID: {$adminRole->id})\n";
    $adminRole->name = 'admin';
    $adminRole->save();
    echo "Renamed to lowercase 'admin'\n";
} else {
    echo "Admin role not found - checking for existing lowercase...\n";
    $existingAdmin = Role::where('name', 'admin')->where('guard_name', 'api')->first();
    if ($existingAdmin) {
        echo "Lowercase 'admin' role already exists (ID: {$existingAdmin->id})\n";
    }
}

// Clear permission cache
app(PermissionRegistrar::class)->forgetCachedPermissions();

// Verify
echo "\nVerifying all roles:\n";
$allRoles = Role::all();
foreach ($allRoles as $role) {
    echo "  - '{$role->name}' (guard: {$role->guard_name}, ID: {$role->id})\n";
}

echo "\nDone!\n";

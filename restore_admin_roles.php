<?php
require __DIR__.'/vendor/autoload.php';

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Restoring admin user roles and permissions for guards web/api...\n";

$adminUser = User::where('email', 'admin@vendorconnect.com')->first();
if (!$adminUser) {
    $adminUser = User::find(42);
}
if (!$adminUser) {
    echo "Admin user not found.\n";
    exit(1);
}

echo "User: {$adminUser->email} (ID: {$adminUser->id})\n";

$roleNames = ['Admin', 'admin'];
$guards = ['web', 'api'];
$permissionNames = ['access_all_data'];

// Ensure permissions exist per guard
foreach ($guards as $guard) {
    foreach ($permissionNames as $permName) {
        Permission::firstOrCreate(['name' => $permName, 'guard_name' => $guard]);
    }
}

// Ensure roles exist per guard
foreach ($guards as $guard) {
    foreach ($roleNames as $rname) {
        Role::firstOrCreate(['name' => $rname, 'guard_name' => $guard]);
    }
}

echo "Syncing all permissions to roles per guard...\n";
foreach ($guards as $guard) {
    $allPerms = Permission::where('guard_name', $guard)->get();
    foreach ($roleNames as $rname) {
        $role = Role::where(['name' => $rname, 'guard_name' => $guard])->first();
        if ($role) {
            $role->syncPermissions($allPerms);
        }
    }
}

echo "Assigning roles to user explicitly with guard context...\n";
foreach ($guards as $guard) {
    foreach ($roleNames as $rname) {
        $role = Role::where(['name' => $rname, 'guard_name' => $guard])->first();
        if ($role) {
            // Assign using Role instance to respect guard
            if (!$adminUser->roles()->where('name', $rname)->where('guard_name', $guard)->exists()) {
                $adminUser->assignRole($role);
            }
        }
    }
}

echo "Granting key permissions directly to user per guard...\n";
foreach ($guards as $guard) {
    foreach ($permissionNames as $permName) {
        $perm = Permission::where(['name' => $permName, 'guard_name' => $guard])->first();
        if ($perm && !$adminUser->permissions()->where('name', $permName)->where('guard_name', $guard)->exists()) {
            $adminUser->givePermissionTo($perm);
        }
    }
}

app(PermissionRegistrar::class)->forgetCachedPermissions();

echo "User roles now: ".implode(', ', $adminUser->roles()->pluck('name')->toArray())."\n";

echo "Total permissions count: ".$adminUser->getAllPermissions()->count()."\n";

echo "Done.\n";

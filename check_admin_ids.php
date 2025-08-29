<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Admin;

echo "Checking admin IDs...\n";
$admins = Admin::all();
echo "Found " . $admins->count() . " admins:\n";
foreach ($admins as $admin) {
    echo "- Admin ID: {$admin->id}, User ID: {$admin->user_id}\n";
}
echo "Done!\n";
?>

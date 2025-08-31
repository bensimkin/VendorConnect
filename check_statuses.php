<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Status;

echo "Available Statuses:\n";
$statuses = Status::all(['id', 'title']);
foreach ($statuses as $status) {
    echo "ID: {$status->id} - Title: {$status->title}\n";
}

echo "\nLooking for 'Active' status...\n";
$activeStatus = Status::where('title', 'Active')->first();
if ($activeStatus) {
    echo "Found Active status with ID: {$activeStatus->id}\n";
} else {
    echo "No 'Active' status found. Available statuses:\n";
    foreach ($statuses as $status) {
        echo "- {$status->title}\n";
    }
}

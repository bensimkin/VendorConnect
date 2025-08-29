<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Priority;

echo "Testing Priority API response structure...\n";

// Test 1: Get priorities with per_page=all
$priorities = Priority::all();
echo "Found " . $priorities->count() . " priorities:\n";
foreach ($priorities as $priority) {
    echo "- ID: {$priority->id}, Title: {$priority->title}, Slug: {$priority->slug}\n";
}

// Test 2: Check if titles are present
echo "\nChecking for empty titles:\n";
$emptyTitles = $priorities->filter(function($priority) {
    return empty($priority->title);
});
if ($emptyTitles->count() > 0) {
    echo "WARNING: Found priorities with empty titles:\n";
    foreach ($emptyTitles as $priority) {
        echo "- ID: {$priority->id}, Title: '{$priority->title}'\n";
    }
} else {
    echo "All priorities have titles.\n";
}

// Test 3: Simulate API response structure
echo "\nSimulating API response structure:\n";
$response = [
    'success' => true,
    'message' => 'Priorities retrieved successfully',
    'data' => $priorities->toArray()
];

echo "Response structure:\n";
echo "- success: " . ($response['success'] ? 'true' : 'false') . "\n";
echo "- message: " . $response['message'] . "\n";
echo "- data count: " . count($response['data']) . "\n";

if (isset($response['data'][0])) {
    echo "- First item keys: " . implode(', ', array_keys($response['data'][0])) . "\n";
    echo "- First item title: " . $response['data'][0]['title'] . "\n";
}

echo "Done!\n";
?>

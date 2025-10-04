<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Cache;

// Clear all login attempt caches
$keys = Cache::get('cache_keys', []);
foreach ($keys as $key) {
    if (strpos($key, 'login_attempts_') === 0) {
        Cache::forget($key);
        echo "Cleared: $key\n";
    }
}

// Also try to clear by pattern (if supported)
Cache::flush();

echo "Login attempts cache cleared!\n";
echo "You can now try logging in again.\n";

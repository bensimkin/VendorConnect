<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing email configuration...\n";
    echo "Mail driver: " . config('mail.default') . "\n";
    echo "Mail host: " . config('mail.mailers.smtp.host') . "\n";
    echo "Mail username: " . config('mail.mailers.smtp.username') . "\n";
    echo "Mail password: " . (config('mail.mailers.smtp.password') ? 'SET' : 'NOT SET') . "\n";
    
    // Test sending an email
    Mail::raw('Test email from VendorConnect', function($message) {
        $message->to('test@example.com')->subject('Test Email');
    });
    
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Email error: " . $e->getMessage() . "\n";
}

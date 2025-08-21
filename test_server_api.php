<?php

$baseUrl = 'https://vc.themastermind.com.au/api/v1';

echo "🧪 TESTING ALL API ENDPOINTS ON SERVER\n";
echo "=====================================\n\n";

// Test results tracking
$totalTests = 0;
$passedTests = 0;
$failedTests = [];

function runTest($testName, $url, $method = 'GET', $data = null, $headers = []) {
    global $totalTests, $passedTests, $failedTests;
    
    $totalTests++;
    echo "Test $totalTests: $testName...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
        'Content-Type: application/json',
        'Accept: application/json'
    ], $headers));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ FAILED: cURL error: $error\n\n";
        $failedTests[] = "$testName (cURL error: $error)";
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ PASSED: HTTP $httpCode\n\n";
        $passedTests++;
        return true;
    } else {
        echo "❌ FAILED: HTTP $httpCode\n";
        if ($response) {
            $result = json_decode($response, true);
            if ($result && isset($result['message'])) {
                echo "   Error: " . $result['message'] . "\n";
            }
        }
        echo "\n";
        $failedTests[] = "$testName (HTTP $httpCode)";
        return false;
    }
}

// 1. Test Login
echo "🔐 AUTHENTICATION TESTS\n";
echo "======================\n";
$loginData = [
    'email' => 'admin@admin.com',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if ($result && $result['success']) {
        $token = $result['data']['token'];
        echo "✅ Login successful! Token received.\n\n";
        
        $authHeaders = ['Authorization: Bearer ' . $token];
        
        // 2. Test Dashboard
        echo "📊 DASHBOARD TESTS\n";
        echo "==================\n";
        runTest('Get Dashboard', $baseUrl . '/dashboard', 'GET', null, $authHeaders);
        
        // 3. Test Tasks
        echo "📋 TASK TESTS\n";
        echo "============\n";
        runTest('Get Tasks', $baseUrl . '/tasks', 'GET', null, $authHeaders);
        runTest('Get Task Types', $baseUrl . '/task-types', 'GET', null, $authHeaders);
        
        // 4. Test Statuses and Priorities
        echo "🏷️ STATUS & PRIORITY TESTS\n";
        echo "==========================\n";
        runTest('Get Statuses', $baseUrl . '/statuses', 'GET', null, $authHeaders);
        runTest('Get Priorities', $baseUrl . '/priorities', 'GET', null, $authHeaders);
        
        // 5. Test Users and Roles
        echo "👥 USER & ROLE TESTS\n";
        echo "====================\n";
        runTest('Get Users', $baseUrl . '/users', 'GET', null, $authHeaders);
        runTest('Get User Roles', $baseUrl . '/user-roles', 'GET', null, $authHeaders);
        
        // 6. Test Clients and Tags
        echo "🏢 CLIENT & TAG TESTS\n";
        echo "=====================\n";
        runTest('Get Clients', $baseUrl . '/clients', 'GET', null, $authHeaders);
        runTest('Get Tags', $baseUrl . '/tags', 'GET', null, $authHeaders);
        
        // 7. Test Profile
        echo "👤 PROFILE TESTS\n";
        echo "================\n";
        runTest('Get Profile', $baseUrl . '/profile', 'GET', null, $authHeaders);
        
        // 8. Test Notifications
        echo "🔔 NOTIFICATION TESTS\n";
        echo "====================\n";
        runTest('Get Notifications', $baseUrl . '/notifications', 'GET', null, $authHeaders);
        
        // 9. Test Projects
        echo "📁 PROJECT TESTS\n";
        echo "================\n";
        runTest('Get Projects', $baseUrl . '/projects', 'GET', null, $authHeaders);
        
        // 10. Test Task Brief Templates
        echo "📝 TASK BRIEF TESTS\n";
        echo "===================\n";
        runTest('Get Task Brief Templates', $baseUrl . '/task-brief-templates', 'GET', null, $authHeaders);
        runTest('Get Task Brief Questions', $baseUrl . '/task-brief-questions', 'GET', null, $authHeaders);
        runTest('Get Task Brief Checklists', $baseUrl . '/task-brief-checklists', 'GET', null, $authHeaders);
        
        // 11. Test Logout
        echo "🚪 LOGOUT TEST\n";
        echo "==============\n";
        runTest('Logout', $baseUrl . '/auth/logout', 'POST', null, $authHeaders);
        
    } else {
        echo "❌ Login failed: Invalid response format\n\n";
        $failedTests[] = "Login (Invalid response format)";
    }
} else {
    echo "❌ Login failed! HTTP Code: $httpCode\n\n";
    $failedTests[] = "Login (HTTP $httpCode)";
}

// Summary
echo "🎯 TEST SUMMARY\n";
echo "===============\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . count($failedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n\n";

if (count($failedTests) > 0) {
    echo "❌ FAILED TESTS:\n";
    foreach ($failedTests as $test) {
        echo "- $test\n";
    }
    echo "\n";
}

if ($passedTests === $totalTests) {
    echo "🎉 ALL TESTS PASSED! API is working perfectly!\n";
} else {
    echo "⚠️ Some tests failed. Check the failed tests above.\n";
}

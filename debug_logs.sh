#!/bin/bash

# VendorConnect Comprehensive Debug Logging Script
# This script monitors all relevant logs and provides detailed debugging information

LOG_DIR="/var/log/nginx"
SERVER_USER="root"
SERVER_HOST="vc.themastermind.com.au"

echo "==================================="
echo "VendorConnect Debug Logging System"
echo "==================================="
echo "Timestamp: $(date)"
echo ""

# Function to check if we can connect to server
check_connection() {
    echo "1. CHECKING SERVER CONNECTION..."
    if ssh -o ConnectTimeout=5 "${SERVER_USER}@${SERVER_HOST}" "echo 'Connected successfully'"; then
        echo "✅ Server connection: OK"
    else
        echo "❌ Server connection: FAILED"
        exit 1
    fi
    echo ""
}

# Function to setup log files with proper permissions
setup_logs() {
    echo "2. SETTING UP LOG FILES..."
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        # Create log files if they don't exist
        touch ${LOG_DIR}/vendorconnect-access.log
        touch ${LOG_DIR}/vendorconnect-error.log
        touch ${LOG_DIR}/api-access.log
        touch ${LOG_DIR}/api-error.log
        touch ${LOG_DIR}/laravel-auth-access.log
        touch ${LOG_DIR}/laravel-auth-error.log
        touch ${LOG_DIR}/root-access.log
        touch ${LOG_DIR}/root-error.log
        touch ${LOG_DIR}/frontend-access.log
        touch ${LOG_DIR}/frontend-error.log
        touch ${LOG_DIR}/php-access.log
        touch ${LOG_DIR}/php-error.log
        
        # Set proper permissions
        chmod 644 ${LOG_DIR}/*.log
        chown www-data:www-data ${LOG_DIR}/vendorconnect-*.log
        chown www-data:www-data ${LOG_DIR}/api-*.log
        chown www-data:www-data ${LOG_DIR}/laravel-auth-*.log
        chown www-data:www-data ${LOG_DIR}/root-*.log
        chown www-data:www-data ${LOG_DIR}/frontend-*.log
        chown www-data:www-data ${LOG_DIR}/php-*.log
        
        echo '✅ Log files created and permissions set'
    "
    echo ""
}

# Function to check file system status
check_filesystem() {
    echo "3. CHECKING FILESYSTEM STATUS..."
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        echo 'Public directory contents:'
        ls -la /var/www/vendorconnect/public/ | head -10
        echo ''
        echo 'Index file details:'
        ls -la /var/www/vendorconnect/public/index.html
        echo ''
        echo 'File permissions on public directory:'
        stat /var/www/vendorconnect/public/
        echo ''
        echo 'Nginx process status:'
        systemctl status nginx --no-pager -l
    "
    echo ""
}

# Function to test request and capture logs
test_request() {
    echo "4. TESTING ROOT REQUEST WITH LIVE LOGGING..."
    
    # Start log monitoring in background
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        echo '--- CLEARING OLD LOGS ---'
        > ${LOG_DIR}/vendorconnect-error.log
        > ${LOG_DIR}/root-error.log
        > ${LOG_DIR}/frontend-error.log
        echo 'Logs cleared'
    " &
    
    sleep 1
    
    echo "Making request to root URL..."
    RESPONSE=$(curl -s -w "HTTP_CODE:%{http_code}\nCONTENT_TYPE:%{content_type}\nTIME_TOTAL:%{time_total}\n" https://vc.themastermind.com.au/)
    
    echo "Response received:"
    echo "$RESPONSE"
    echo ""
    
    sleep 2
    
    echo "5. ANALYZING LOGS AFTER REQUEST..."
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        echo '--- NGINX ERROR LOG (last 5 lines) ---'
        tail -5 ${LOG_DIR}/error.log
        echo ''
        echo '--- VENDORCONNECT ERROR LOG ---'
        cat ${LOG_DIR}/vendorconnect-error.log
        echo ''
        echo '--- ROOT LOCATION ERROR LOG ---'
        cat ${LOG_DIR}/root-error.log
        echo ''
        echo '--- FRONTEND LOCATION ERROR LOG ---'
        cat ${LOG_DIR}/frontend-error.log
        echo ''
        echo '--- ACCESS LOGS (last 3 lines) ---'
        tail -3 ${LOG_DIR}/access.log
        echo ''
        echo '--- VENDORCONNECT ACCESS LOG ---'
        cat ${LOG_DIR}/vendorconnect-access.log
    "
    echo ""
}

# Function to analyze Nginx configuration
analyze_config() {
    echo "6. ANALYZING NGINX CONFIGURATION..."
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        echo '--- NGINX CONFIGURATION TEST ---'
        nginx -t
        echo ''
        echo '--- ACTIVE NGINX CONFIGURATION ---'
        nginx -T | grep -A 50 'server_name vc.themastermind.com.au' | head -30
        echo ''
        echo '--- LOCATION BLOCKS ---'
        nginx -T | grep -A 3 'location'
    "
    echo ""
}

# Function to check system resources
check_resources() {
    echo "7. CHECKING SYSTEM RESOURCES..."
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        echo '--- DISK SPACE ---'
        df -h /var/www/vendorconnect/
        echo ''
        echo '--- MEMORY USAGE ---'
        free -h
        echo ''
        echo '--- NGINX PROCESS ---'
        ps aux | grep nginx
        echo ''
        echo '--- PHP-FPM PROCESS ---'
        ps aux | grep php-fpm | head -3
    "
    echo ""
}

# Function to test specific files
test_files() {
    echo "8. TESTING SPECIFIC FILE ACCESS..."
    
    echo "Testing index.html directly:"
    INDEX_RESPONSE=$(curl -s -w "HTTP_CODE:%{http_code}\n" https://vc.themastermind.com.au/index.html)
    echo "Response: $INDEX_RESPONSE"
    echo ""
    
    echo "Testing a simple file (test.html):"
    TEST_RESPONSE=$(curl -s -w "HTTP_CODE:%{http_code}\n" https://vc.themastermind.com.au/test.html)
    echo "Response: $TEST_RESPONSE"
    echo ""
    
    echo "Testing API endpoint:"
    API_RESPONSE=$(curl -s -w "HTTP_CODE:%{http_code}\n" https://vc.themastermind.com.au/api/v1/auth/user)
    echo "Response: $API_RESPONSE"
    echo ""
}

# Function to monitor logs in real-time
monitor_realtime() {
    echo "9. REAL-TIME LOG MONITORING (Press Ctrl+C to stop)..."
    echo "Open another terminal and access https://vc.themastermind.com.au/ to see live logs"
    echo ""
    
    ssh "${SERVER_USER}@${SERVER_HOST}" "
        tail -f ${LOG_DIR}/vendorconnect-error.log ${LOG_DIR}/root-error.log ${LOG_DIR}/frontend-error.log ${LOG_DIR}/error.log
    "
}

# Main execution
main() {
    case "${1:-all}" in
        "connection")
            check_connection
            ;;
        "setup")
            check_connection
            setup_logs
            ;;
        "filesystem")
            check_connection
            check_filesystem
            ;;
        "test")
            check_connection
            test_request
            ;;
        "config")
            check_connection
            analyze_config
            ;;
        "resources")
            check_connection
            check_resources
            ;;
        "files")
            check_connection
            test_files
            ;;
        "monitor")
            check_connection
            monitor_realtime
            ;;
        "all")
            check_connection
            setup_logs
            check_filesystem
            analyze_config
            test_request
            test_files
            check_resources
            echo "==================================="
            echo "DEBUG COMPLETE"
            echo "==================================="
            echo ""
            echo "To monitor logs in real-time, run:"
            echo "./debug_logs.sh monitor"
            ;;
        *)
            echo "Usage: $0 [connection|setup|filesystem|test|config|resources|files|monitor|all]"
            echo ""
            echo "Options:"
            echo "  connection  - Test server connection"
            echo "  setup      - Setup log files"
            echo "  filesystem - Check file system status"
            echo "  test       - Test root request with logging"
            echo "  config     - Analyze nginx configuration"
            echo "  resources  - Check system resources"
            echo "  files      - Test specific file access"
            echo "  monitor    - Real-time log monitoring"
            echo "  all        - Run all checks (default)"
            ;;
    esac
}

main "$@"

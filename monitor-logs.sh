#!/bin/bash

# VendorConnect Comprehensive Log Monitor
# This script monitors all relevant logs for the application

echo "🔍 VendorConnect Log Monitor Started"
echo "======================================"
echo "Monitoring logs for: $(date)"
echo ""

# Function to monitor Laravel logs
monitor_laravel_logs() {
    echo "📝 Laravel Application Logs:"
    echo "----------------------------"
    tail -f /var/www/vendorconnect/storage/logs/laravel.log | while read line; do
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] LARAVEL: $line"
    done &
}

# Function to monitor Nginx access logs
monitor_nginx_access() {
    echo "🌐 Nginx Access Logs:"
    echo "---------------------"
    tail -f /var/log/nginx/access.log | while read line; do
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] NGINX_ACCESS: $line"
    done &
}

# Function to monitor Nginx error logs
monitor_nginx_errors() {
    echo "❌ Nginx Error Logs:"
    echo "-------------------"
    tail -f /var/log/nginx/error.log | while read line; do
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] NGINX_ERROR: $line"
    done &
}

# Function to monitor PHP-FPM logs
monitor_php_fpm() {
    echo "🐘 PHP-FPM Logs:"
    echo "---------------"
    tail -f /var/log/php8.2-fpm.log | while read line; do
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] PHP_FPM: $line"
    done &
}

# Function to monitor MySQL logs
monitor_mysql() {
    echo "🗄️ MySQL Logs:"
    echo "-------------"
    tail -f /var/log/mysql/error.log | while read line; do
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] MYSQL: $line"
    done &
}

# Function to monitor system logs
monitor_system() {
    echo "🖥️ System Logs:"
    echo "--------------"
    tail -f /var/log/syslog | grep -E "(nginx|php|mysql|vendorconnect)" | while read line; do
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] SYSTEM: $line"
    done &
}

# Function to show recent errors
show_recent_errors() {
    echo "🚨 Recent Errors Summary:"
    echo "========================="
    echo ""
    
    echo "📝 Recent Laravel Errors:"
    tail -20 /var/www/vendorconnect/storage/logs/laravel.log | grep -i "error\|exception\|fatal" || echo "No recent Laravel errors"
    echo ""
    
    echo "🌐 Recent Nginx Errors:"
    tail -10 /var/log/nginx/error.log || echo "No recent Nginx errors"
    echo ""
    
    echo "🐘 Recent PHP-FPM Errors:"
    tail -10 /var/log/php8.2-fpm.log | grep -i "error\|warning" || echo "No recent PHP-FPM errors"
    echo ""
    
    echo "🗄️ Recent MySQL Errors:"
    tail -10 /var/log/mysql/error.log | grep -i "error\|warning" || echo "No recent MySQL errors"
    echo ""
}

# Function to show application status
show_status() {
    echo "📊 Application Status:"
    echo "====================="
    echo ""
    
    echo "🔧 Services Status:"
    systemctl is-active nginx && echo "✅ Nginx: Running" || echo "❌ Nginx: Not Running"
    systemctl is-active php8.2-fpm && echo "✅ PHP-FPM: Running" || echo "❌ PHP-FPM: Not Running"
    systemctl is-active mysql && echo "✅ MySQL: Running" || echo "❌ MySQL: Not Running"
    echo ""
    
    echo "💾 Disk Usage:"
    df -h /var/www/vendorconnect
    echo ""
    
    echo "🧠 Memory Usage:"
    free -h
    echo ""
    
    echo "🌐 Network Connections:"
    netstat -tlnp | grep :80
    echo ""
}

# Main execution
case "$1" in
    "monitor")
        echo "Starting real-time log monitoring..."
        monitor_laravel_logs
        monitor_nginx_access
        monitor_nginx_errors
        monitor_php_fpm
        monitor_mysql
        monitor_system
        wait
        ;;
    "errors")
        show_recent_errors
        ;;
    "status")
        show_status
        ;;
    "all")
        show_status
        echo ""
        show_recent_errors
        echo ""
        echo "Starting real-time monitoring..."
        monitor_laravel_logs
        monitor_nginx_access
        monitor_nginx_errors
        monitor_php_fpm
        monitor_mysql
        monitor_system
        wait
        ;;
    *)
        echo "Usage: $0 {monitor|errors|status|all}"
        echo ""
        echo "Commands:"
        echo "  monitor  - Start real-time log monitoring"
        echo "  errors   - Show recent errors from all logs"
        echo "  status   - Show application and system status"
        echo "  all      - Show status, errors, then start monitoring"
        echo ""
        echo "Examples:"
        echo "  $0 monitor    # Start monitoring all logs"
        echo "  $0 errors     # Show recent errors"
        echo "  $0 status     # Show system status"
        echo "  $0 all        # Full monitoring with status"
        ;;
esac

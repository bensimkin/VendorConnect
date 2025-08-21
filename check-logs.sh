#!/bin/bash

# Local script to check VendorConnect logs remotely
# Usage: ./check-logs.sh [command]

SERVER="vendorconnect"
APP_PATH="/var/www/vendorconnect"

case "$1" in
    "laravel")
        echo "📝 Laravel Logs:"
        ssh $SERVER "tail -50 $APP_PATH/storage/logs/laravel.log"
        ;;
    "nginx")
        echo "🌐 Nginx Logs:"
        ssh $SERVER "tail -20 /var/log/nginx/error.log"
        ;;
    "php")
        echo "🐘 PHP-FPM Logs:"
        ssh $SERVER "tail -20 /var/log/php8.2-fpm.log"
        ;;
    "mysql")
        echo "🗄️ MySQL Logs:"
        ssh $SERVER "tail -20 /var/log/mysql/error.log"
        ;;
    "errors")
        echo "🚨 Recent Errors:"
        ssh $SERVER "cd $APP_PATH && ./monitor-logs.sh errors"
        ;;
    "status")
        echo "📊 System Status:"
        ssh $SERVER "cd $APP_PATH && ./monitor-logs.sh status"
        ;;
    "monitor")
        echo "🔍 Starting Real-time Monitoring:"
        ssh $SERVER "cd $APP_PATH && ./monitor-logs.sh monitor"
        ;;
    "all")
        echo "🔍 Full Monitoring:"
        ssh $SERVER "cd $APP_PATH && ./monitor-logs.sh all"
        ;;
    "clear")
        echo "🧹 Clearing Laravel Caches:"
        ssh $SERVER "cd $APP_PATH && php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"
        ;;
    "restart")
        echo "🔄 Restarting Services:"
        ssh $SERVER "systemctl restart nginx php8.2-fpm mysql"
        ;;
    *)
        echo "Usage: $0 {laravel|nginx|php|mysql|errors|status|monitor|all|clear|restart}"
        echo ""
        echo "Commands:"
        echo "  laravel  - Show recent Laravel logs"
        echo "  nginx    - Show recent Nginx error logs"
        echo "  php      - Show recent PHP-FPM logs"
        echo "  mysql    - Show recent MySQL logs"
        echo "  errors   - Show recent errors from all sources"
        echo "  status   - Show system and application status"
        echo "  monitor  - Start real-time log monitoring"
        echo "  all      - Full monitoring with status"
        echo "  clear    - Clear Laravel caches"
        echo "  restart  - Restart all services"
        echo ""
        echo "Examples:"
        echo "  $0 laravel    # Check Laravel logs"
        echo "  $0 errors     # Check recent errors"
        echo "  $0 monitor    # Start monitoring"
        echo "  $0 status     # Check system status"
        ;;
esac

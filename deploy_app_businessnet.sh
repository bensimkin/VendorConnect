#!/bin/bash
# =============================================================================
# VendorConnect Deployment Script for app.businessnet.com
# =============================================================================
# 
# This script deploys the latest VendorConnect changes to app.businessnet.com
# It updates both the Laravel backend and Next.js frontend
#
# USAGE:
#   ./deploy_app_businessnet.sh
#
# =============================================================================

set -e

# Configuration
SERVER_HOST="app.businessnet.com"
SERVER_USER="root"
REMOTE_DIR="/var/www/vendorconnect"
FRONTEND_DIR="/var/www/vendorconnect/vendorconnect-frontend"
LOG_FILE="/tmp/vendorconnect-deployment-$(date +%Y%m%d-%H%M%S).log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    local level="$1"
    local message="$2"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local log_entry="[$timestamp] [$level] $message"
    
    echo -e "$log_entry"
    echo "$log_entry" >> "$LOG_FILE"
}

log_info() { log "INFO" "$1"; }
log_success() { log "SUCCESS" "$1"; }
log_warning() { log "WARNING" "$1"; }
log_error() { log "ERROR" "$1"; }

# Function to run command on server
run_server_command() {
    local command="$1"
    local description="$2"
    
    log_info "Executing: $description"
    log_info "Command: $command"
    
    if ssh "$SERVER_USER@$SERVER_HOST" "$command"; then
        log_success "$description completed successfully"
    else
        log_error "$description failed"
        exit 1
    fi
}

# Function to copy files to server
copy_files_to_server() {
    local local_path="$1"
    local remote_path="$2"
    local description="$3"
    
    log_info "Copying: $description"
    log_info "From: $local_path"
    log_info "To: $remote_path"
    
    if scp -r "$local_path" "$SERVER_USER@$SERVER_HOST:$remote_path"; then
        log_success "$description completed successfully"
    else
        log_error "$description failed"
        exit 1
    fi
}

# Main deployment function
main() {
    log_info "=== Starting VendorConnect Deployment to app.businessnet.com ==="
    log_info "Server: $SERVER_HOST"
    log_info "Log file: $LOG_FILE"
    
    # Check server connectivity
    log_info "=== Checking Server Connectivity ==="
    if ssh "$SERVER_USER@$SERVER_HOST" "echo 'Connection successful'"; then
        log_success "Server connection established"
    else
        log_error "Cannot connect to server $SERVER_HOST"
        exit 1
    fi
    
    # Backup current deployment
    log_info "=== Creating Backup ==="
    run_server_command "cd /var/www && tar -czf vendorconnect-backup-$(date +%Y%m%d-%H%M%S).tar.gz vendorconnect/" "Creating backup of current deployment"
    
    # Update backend Laravel application
    log_info "=== Updating Backend Laravel Application ==="
    run_server_command "cd $REMOTE_DIR && git pull origin main" "Pulling latest changes from git"
    run_server_command "cd $REMOTE_DIR && composer install --no-dev --optimize-autoloader" "Installing PHP dependencies"
    run_server_command "cd $REMOTE_DIR && php artisan migrate --force" "Running database migrations"
    run_server_command "cd $REMOTE_DIR && php artisan config:clear && php artisan route:clear && php artisan cache:clear" "Clearing Laravel caches"
    run_server_command "cd $REMOTE_DIR && php artisan config:cache && php artisan route:cache" "Rebuilding Laravel caches"
    
    # Update frontend Next.js application
    log_info "=== Updating Frontend Next.js Application ==="
    run_server_command "cd $FRONTEND_DIR && npm install" "Installing Node.js dependencies"
    run_server_command "cd $FRONTEND_DIR && npm run build" "Building Next.js frontend"
    
    # Set proper permissions
    log_info "=== Setting Permissions ==="
    run_server_command "chown -R www-data:www-data $REMOTE_DIR" "Setting ownership"
    run_server_command "chmod -R 755 $REMOTE_DIR" "Setting base permissions"
    run_server_command "chmod -R 775 $REMOTE_DIR/storage $REMOTE_DIR/bootstrap/cache" "Setting storage permissions"
    
    # Restart services
    log_info "=== Restarting Services ==="
    run_server_command "systemctl restart php8.3-fpm" "Restarting PHP-FPM"
    run_server_command "systemctl reload nginx" "Reloading Nginx"
    run_server_command "pm2 restart vendorconnect-frontend" "Restarting Next.js frontend"
    
    # Wait for services to be ready
    log_info "=== Waiting for Services ==="
    sleep 10
    
    # Test the application
    log_info "=== Testing Application ==="
    if curl -s -f "https://app.businessnet.com/api/v1" >/dev/null; then
        log_success "API is responding"
    else
        log_warning "API test failed - may need manual verification"
    fi
    
    if curl -s -f "https://app.businessnet.com/" >/dev/null; then
        log_success "Frontend is responding"
    else
        log_warning "Frontend test failed - may need manual verification"
    fi
    
    # Final success message
    log_success "=== Deployment Completed Successfully ==="
    log_info "Backend Laravel application updated"
    log_info "Frontend Next.js application updated"
    log_info "All services restarted"
    log_info "Log file saved to: $LOG_FILE"
    log_info ""
    log_info "🌐 Application URL: https://app.businessnet.com"
    log_info "🔗 API URL: https://app.businessnet.com/api/v1"
    log_info ""
    log_info "📊 Deployment Summary:"
    log_info "- Pulled latest changes from git"
    log_info "- Updated PHP dependencies"
    log_info "- Ran database migrations"
    log_info "- Rebuilt Laravel caches"
    log_info "- Updated Node.js dependencies"
    log_info "- Built Next.js frontend"
    log_info "- Restarted all services"
    log_info ""
    log_info "🎉 Your VendorConnect application is now updated and running!"
}

# Run main function
main "$@"

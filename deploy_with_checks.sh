#!/bin/bash
# =============================================================================
# VendorConnect Deployment Script with Health Checks
# =============================================================================
# 
# This script deploys VendorConnect with comprehensive health checks to prevent
# environment variable issues and ensure Smart API functionality.
#
# USAGE:
#   ./deploy_with_checks.sh [SERVER_ALIAS]
#
# EXAMPLE:
#   ./deploy_with_checks.sh app-businessnet
#
# =============================================================================

set -e

# Configuration
SERVER_ALIAS=${1:-app-businessnet}
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
    
    if ssh "$SERVER_ALIAS" "$command"; then
        log_success "$description completed successfully"
    else
        log_error "$description failed"
        exit 1
    fi
}

# Function to check if command exists on server
check_server_command() {
    local command="$1"
    local description="$2"
    
    if ssh "$SERVER_ALIAS" "command -v $command >/dev/null 2>&1"; then
        log_success "$description is available"
    else
        log_error "$description is not available on server"
        exit 1
    fi
}

# Function to verify environment variables
verify_environment_variables() {
    log_info "=== Verifying Environment Variables ==="
    
    # Check if .env file exists and has required variables
    if ssh "$SERVER_ALIAS" "cd /var/www/vendorconnect && test -f .env && grep -q 'SMART_API_KEY' .env && grep -q 'OPENAI_API_KEY' .env"; then
        log_success ".env file exists and contains required API keys"
    else
        log_error ".env file missing or incomplete"
        exit 1
    fi
    
    # Check if PHP-FPM configuration has environment variables
    if ssh "$SERVER_ALIAS" "grep -q 'env\[SMART_API_KEY\]' /etc/php/8.3/fpm/pool.d/www.conf && grep -q 'env\[OPENAI_API_KEY\]' /etc/php/8.3/fpm/pool.d/www.conf"; then
        log_success "PHP-FPM configuration contains required environment variables"
    else
        log_warning "PHP-FPM configuration missing environment variables - adding them"
        
        # Add environment variables to PHP-FPM config
        ssh "$SERVER_ALIAS" "echo 'env[SMART_API_KEY] = your_smart_api_key_here' >> /etc/php/8.3/fpm/pool.d/www.conf"
        ssh "$SERVER_ALIAS" "echo 'env[OPENAI_API_KEY] = your_openai_api_key_here' >> /etc/php/8.3/fpm/pool.d/www.conf"
        
        log_success "Environment variables added to PHP-FPM configuration"
    fi
}

# Function to run health checks
run_health_checks() {
    log_info "=== Running Health Checks ==="
    
    # Wait for services to be ready
    sleep 5
    
    # Test Smart API health endpoint
    log_info "Testing Smart API health endpoint..."
    if curl -s -f "https://app.businessnet.com/api/v1/health/smart-api" >/dev/null; then
        log_success "Smart API health endpoint is responding"
    else
        log_error "Smart API health endpoint is not responding"
        exit 1
    fi
    
    # Test Smart API functionality
    log_info "Testing Smart API user listing..."
    local smart_api_response=$(curl -s -X POST -H 'Content-Type: application/json' -H 'X-API-Key: vck_IuYqGalsAzWt6TP8y2eg0ZhRj3sJNekU8lonoOtI' -d '{"action":"get_users","params":{}}' 'https://app.businessnet.com/api/v1/smart-task')
    
    if echo "$smart_api_response" | jq -e '.success == true and .data | length > 0' >/dev/null; then
        local user_count=$(echo "$smart_api_response" | jq '.data | length')
        log_success "Smart API is working correctly - found $user_count users"
    else
        log_error "Smart API is not working correctly"
        log_error "Response: $smart_api_response"
        exit 1
    fi
    
    # Test main application
    log_info "Testing main application..."
    if curl -s -f "https://app.businessnet.com/" >/dev/null; then
        log_success "Main application is responding"
    else
        log_error "Main application is not responding"
        exit 1
    fi
}

# Main deployment function
main() {
    log_info "=== Starting VendorConnect Deployment with Health Checks ==="
    log_info "Server: $SERVER_ALIAS"
    log_info "Log file: $LOG_FILE"
    
    # Check server connectivity
    log_info "=== Checking Server Connectivity ==="
    if ssh "$SERVER_ALIAS" "echo 'Connection successful'"; then
        log_success "Server connection established"
    else
        log_error "Cannot connect to server $SERVER_ALIAS"
        exit 1
    fi
    
    # Check required commands
    log_info "=== Checking Required Commands ==="
    check_server_command "php" "PHP"
    check_server_command "composer" "Composer"
    check_server_command "npm" "NPM"
    check_server_command "pm2" "PM2"
    
    # Verify environment variables
    verify_environment_variables
    
    # Deploy application
    log_info "=== Deploying Application ==="
    run_server_command "cd /var/www/vendorconnect && git pull origin main" "Pull latest changes"
    run_server_command "cd /var/www/vendorconnect && composer install --no-dev --optimize-autoloader" "Install PHP dependencies"
    run_server_command "cd /var/www/vendorconnect && php artisan migrate --force" "Run database migrations"
    run_server_command "cd /var/www/vendorconnect && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache" "Clear and rebuild caches"
    run_server_command "cd /var/www/vendorconnect/vendorconnect-frontend && npm install && npm run build" "Build frontend"
    run_server_command "chown -R www-data:www-data /var/www/vendorconnect && chmod -R 755 /var/www/vendorconnect && chmod -R 775 /var/www/vendorconnect/storage /var/www/vendorconnect/bootstrap/cache" "Set permissions"
    run_server_command "systemctl restart php8.3-fpm && systemctl reload nginx && pm2 restart vendorconnect-frontend" "Restart services"
    
    # Run health checks
    run_health_checks
    
    # Final success message
    log_success "=== Deployment Completed Successfully ==="
    log_info "All health checks passed"
    log_info "Smart API is working correctly"
    log_info "Application is ready for use"
    log_info "Log file saved to: $LOG_FILE"
}

# Run main function
main "$@"

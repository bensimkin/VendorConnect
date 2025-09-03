#!/bin/bash
# =============================================================================
# VendorConnect Deployment Script
# =============================================================================
# 
# This script deploys a complete VendorConnect application stack including:
# - Laravel backend API
# - Next.js frontend
# - MySQL database
# - Redis cache
# - Nginx web server
# - SSL certificates (via Certbot)
# - PM2 process management
#
# USAGE:
#   ./deploy.sh [OPTIONS]
#
# ENVIRONMENT VARIABLES:
#   SOURCE_SERVER    - Optional: Hostname/IP of existing server to copy data from
#   DB_PASSWORD     - Database password (defaults to 'your_secure_password_here')
#
# REQUIREMENTS:
#   - Ubuntu 20.04+ server
#   - Root access
#   - Internet connectivity
#   - SSH key access to SOURCE_SERVER (if specified)
#
# SECURITY NOTES:
#   - Change default database password before production use
#   - Review and customize all configuration files
#   - Ensure proper firewall and security settings
#
# =============================================================================
set -e

# Enhanced logging setup
LOG_FILE="/var/log/vendorconnect-deployment.log"
ERROR_LOG="/var/log/vendorconnect-deployment-errors.log"
DEBUG_LOG="/var/log/vendorconnect-deployment-debug.log"

# Create log directory if it doesn't exist
mkdir -p /var/log

# Function to get current timestamp
timestamp() {
    date '+%Y-%m-%d %H:%M:%S.%3N'
}

# Function to log with different levels
log() {
    local level="$1"
    local message="$2"
    local timestamp=$(timestamp)
    local log_entry="[$timestamp] [$level] $message"
    
    # Always output to console
    echo "$log_entry"
    
    # Write to main log file
    echo "$log_entry" >> "$LOG_FILE"
    
    # Write errors to error log
    if [[ "$level" == "ERROR" ]]; then
        echo "$log_entry" >> "$ERROR_LOG"
    fi
    
    # Write debug info to debug log
    if [[ "$level" == "DEBUG" ]]; then
        echo "$log_entry" >> "$DEBUG_LOG"
    fi
}

# Log levels
log_info() { log "INFO" "$1"; }
log_success() { log "SUCCESS" "$1"; }
log_warning() { log "WARNING" "$1"; }
log_error() { log "ERROR" "$1"; }
log_debug() { log "DEBUG" "$1"; }

# Function to log system information
log_system_info() {
    log_info "=== System Information ==="
    log_info "Hostname: $(hostname)"
    log_info "OS: $(cat /etc/os-release | grep PRETTY_NAME | cut -d'"' -f2)"
    log_info "Kernel: $(uname -r)"
    log_info "Architecture: $(uname -m)"
    log_info "CPU: $(nproc) cores"
    log_info "Memory: $(free -h | grep Mem | awk '{print $2}')"
    log_info "Disk: $(df -h / | tail -1 | awk '{print $2}')"
    log_info "Current user: $(whoami)"
    log_info "Working directory: $(pwd)"
    log_info "Date: $(date)"
    log_info "Uptime: $(uptime)"
    
    # CRITICAL: Log available resources
    log_info "=== Available Resources ==="
    log_info "Available disk space:"
    df -h >> "$LOG_FILE"
    log_info "Available memory:"
    free -h >> "$LOG_FILE"
    log_info "Available swap:"
    swapon --show >> "$LOG_FILE" 2>&1 || log_warning "No swap configured"
}

# Function to check if we have enough resources
check_resources() {
    log_info "=== Checking Available Resources ==="
    
    # Check disk space (need at least 5GB free)
    local available_disk=$(df / | awk 'NR==2 {print $4}')
    local available_disk_gb=$((available_disk / 1024 / 1024))
    
    if [ "$available_disk_gb" -lt 5 ]; then
        log_error "Insufficient disk space: ${available_disk_gb}GB available, need at least 5GB"
        exit 1
    fi
    log_success "Disk space OK: ${available_disk_gb}GB available"
    
    # Check memory (need at least 1GB)
    local available_mem=$(free -m | grep Mem | awk '{print $2}')
    if [ "$available_mem" -lt 1024 ]; then
        log_warning "Low memory: ${available_mem}MB available, may need swap file"
    else
        log_success "Memory OK: ${available_mem}MB available"
    fi
}

# Function to install packages with retry logic
install_packages() {
    local packages=("$@")
    local max_attempts=3
    
    for package in "${packages[@]}"; do
        local attempt=1
        while [ $attempt -le $max_attempts ]; do
            log_info "Installing $package (attempt $attempt/$max_attempts)"
            if apt-get install -y "$package" 2>/tmp/package_error.log; then
                log_success "$package installed successfully"
                break
            else
                log_warning "Failed to install $package (attempt $attempt)"
                if [ $attempt -eq $max_attempts ]; then
                    log_error "Failed to install $package after $max_attempts attempts"
                    cat /tmp/package_error.log
                    exit 1
                fi
                attempt=$((attempt + 1))
                sleep 5
            fi
        done
    done
}

# Function to log command execution
log_command() {
    local command="$1"
    local description="$2"
    log_info "Executing: $description"
    log_debug "Command: $command"
    
    if eval "$command"; then
        log_success "$description completed successfully"
    else
        log_error "$description failed"
        exit 1
    fi
}

# Function to check command status
check_status() {
    local operation="$1"
    if [ $? -eq 0 ]; then
        log_success "$operation completed successfully"
    else
        log_error "$operation failed"
        exit 1
    fi
}

# Function to setup swap if needed
setup_swap() {
    local mem_gb=$(free -g | grep Mem | awk '{print $2}')
    if [ "$mem_gb" -lt 2 ]; then
        log_warning "Low memory detected ($mem_gb GB), setting up swap file"
        if [ ! -f /swapfile ]; then
            log_command "dd if=/dev/zero of=/swapfile bs=1M count=2048" "Creating 2GB swap file"
            log_command "chmod 600 /swapfile" "Setting swap file permissions"
            log_command "mkswap /swapfile" "Setting up swap"
            log_command "swapon /swapfile" "Enabling swap"
            log_command "echo '/swapfile none swap sw 0 0' >> /etc/fstab" "Adding swap to fstab"
            log_success "Swap file created and enabled"
        else
            log_info "Swap file already exists"
        fi
    else
        log_info "Sufficient memory ($mem_gb GB), no swap needed"
    fi
}

# Function to verify services are running
verify_services() {
    log_info "=== Verifying Services ==="
    
    # Check MySQL
    if systemctl is-active --quiet mysql; then
        log_success "MySQL is running"
    else
        log_error "MySQL is not running"
        exit 1
    fi
    
    # Check Redis
    if systemctl is-active --quiet redis-server; then
        log_success "Redis is running"
    else
        log_error "Redis is not running"
        exit 1
    fi
    
    # Check PHP-FPM (detect version)
    local php_version=""
    if systemctl is-active --quiet php8.3-fpm; then
        php_version="8.3"
    elif systemctl is-active --quiet php8.2-fpm; then
        php_version="8.2"
    elif systemctl is-active --quiet php8.1-fpm; then
        php_version="8.1"
    else
        log_error "No PHP-FPM service found running"
        exit 1
    fi
    log_success "PHP-FPM $php_version is running"
    
    # Check Nginx
    if systemctl is-active --quiet nginx; then
        log_success "Nginx is running"
    else
        log_error "Nginx is not running"
        exit 1
    fi
}

# Main deployment function
main() {
    log_info "=== Starting VendorConnect Deployment ==="
    log_system_info
    check_resources
    setup_swap
    
    # Update system packages
    log_info "=== Updating System Packages ==="
    log_command "apt-get update" "Updating package lists"
    
    # Install required packages in groups with retry logic
    log_info "=== Installing System Packages ==="
    
    # Group 1: Essential packages
    log_info "Installing essential packages..."
    install_packages "curl" "wget" "git" "unzip" "software-properties-common"
    
    # Group 2: Web server packages
    log_info "Installing web server packages..."
    install_packages "nginx" "apache2-utils"
    
    # Group 3: Database packages
    log_info "Installing database packages..."
    install_packages "mysql-server" "redis-server"
    
    # Group 4: PHP packages (detect available version)
    log_info "Detecting available PHP version..."
    if apt-cache show php8.3-fpm >/dev/null 2>&1; then
        php_version="8.3"
    elif apt-cache show php8.2-fpm >/dev/null 2>&1; then
        php_version="8.2"
    elif apt-cache show php8.1-fpm >/dev/null 2>&1; then
        php_version="8.1"
    else
        log_error "No supported PHP version found (8.1, 8.2, or 8.3)"
        exit 1
    fi
    
    log_info "Installing PHP $php_version packages..."
    install_packages "php$php_version-fpm" "php$php_version-mysql" "php$php_version-xml" "php$php_version-mbstring" "php$php_version-curl" "php$php_version-zip" "php$php_version-gd"
    
    # Group 5: Development tools
    log_info "Installing development tools..."
    install_packages "composer" "nodejs" "npm"
    
    # Start and enable services
    log_info "=== Starting Services ==="
    log_command "systemctl enable mysql" "Enabling MySQL service"
    log_command "systemctl start mysql" "Starting MySQL service"
    check_status "MySQL service start"
    
    # Wait for MySQL to be ready
    log_info "=== Waiting for MySQL to be Ready ==="
    log_info "Waiting 5 seconds for MySQL to start..."
    sleep 5
    
    log_info "Waiting for MySQL to accept connections..."
    until mysqladmin ping -h localhost --silent; do
        log_debug "MySQL not ready yet, waiting..."
        sleep 2
    done
    log_success "MySQL is ready and accepting connections"
    
    log_command "systemctl enable redis-server" "Enabling Redis service"
    log_command "systemctl start redis-server" "Starting Redis service"
    check_status "Redis service start"
    
    log_command "systemctl enable php$php_version-fpm" "Enabling PHP-FPM service"
    log_command "systemctl start php$php_version-fpm" "Starting PHP-FPM service"
    check_status "PHP-FPM service start"
    
    # Wait for services to be ready
    log_info "Waiting for services to be fully ready..."
    sleep 15
    
    # Verify all services are running
    verify_services
    
    # Install PM2 globally
    log_info "=== Installing PM2 ==="
    log_command "npm install -g pm2" "Installing PM2 globally"
    check_status "PM2 installation"
    
    # CRITICAL FIX: Create correct directory structure to match working production server
    log_info "=== Setting up Correct Directory Structure ==="
    log_command "mkdir -p /var/www/vendorconnect" "Creating Laravel application directory"
    log_command "mkdir -p /var/www/vendorconnect-frontend" "Creating Next.js frontend directory"
    
    # Clone VendorConnect repository to Laravel directory
    log_info "=== Cloning Repository ==="
    if ! git clone https://github.com/bensimkin/VendorConnect.git /tmp/vendorconnect-temp; then
        log_error "Repository cloning failed"
        exit 1
    fi
    check_status "Repository cloning"
    
    # Move Laravel files to correct location
    log_info "=== Moving Laravel Files ==="
    log_command "cp -r /tmp/vendorconnect-temp/* /var/www/vendorconnect/" "Moving Laravel files"
    log_command "cp -r /tmp/vendorconnect-temp/.* /var/www/vendorconnect/" "Moving hidden Laravel files"
    
    # Move frontend to separate directory (matching production server structure)
    log_info "=== Moving Frontend Files ==="
    if [ -d "/var/www/vendorconnect/vendorconnect-frontend" ]; then
        log_command "mv /var/www/vendorconnect/vendorconnect-frontend/* /var/www/vendorconnect-frontend/" "Moving frontend files"
        log_command "mv /var/www/vendorconnect/vendorconnect-frontend/.* /var/www/vendorconnect-frontend/" "Moving hidden frontend files" 2>/dev/null || true
        log_command "rmdir /var/www/vendorconnect/vendorconnect-frontend" "Removing empty frontend directory from Laravel"
    else
        log_error "Frontend directory not found in repository"
        exit 1
    fi
    
    # Clean up temp directory
    log_command "rm -rf /tmp/vendorconnect-temp" "Cleaning up temporary files"
    
    # Validate repository structure
    log_info "=== Validating Repository Structure ==="
    if [ ! -f "/var/www/vendorconnect/artisan" ]; then
        log_error "Invalid Laravel repository - artisan file not found"
        exit 1
    fi
    log_success "Laravel artisan file found - valid repository"
    
    if [ ! -d "/var/www/vendorconnect-frontend" ]; then
        log_error "Frontend directory not found. Repository structure may have changed."
        exit 1
    fi
    log_success "Frontend directory found"
    
    # Install PHP dependencies
    log_info "=== Installing PHP Dependencies ==="
    cd /var/www/vendorconnect
    
    if ! composer install --no-dev --optimize-autoloader 2>/tmp/composer_error.log; then
        log_error "Composer install failed"
        cat /tmp/composer_error.log
        exit 1
    fi
    check_status "Composer install"
    
    # Install Node.js dependencies
    log_info "=== Installing Node.js Dependencies ==="
    cd /var/www/vendorconnect-frontend
    
    # Set memory limits for npm build
    mem_gb=$(free -g | grep Mem | awk '{print $2}')
    if [ "$mem_gb" -lt 4 ]; then
        log_warning "Low memory detected ($mem_gb GB), setting NODE_OPTIONS for npm build"
        export NODE_OPTIONS="--max_old_space_size=1024"
        log_info "Set NODE_OPTIONS=--max_old_space_size=1024"
    else
        log_info "Sufficient memory ($mem_gb GB), using default npm settings"
    fi
    
    if ! npm install 2>/tmp/npm_install_error.log; then
        log_error "NPM install failed"
        cat /tmp/npm_install_error.log
        exit 1
    fi
    check_status "NPM install"
    
    # CRITICAL FIX: Update Next.js configuration to use correct domain
    log_info "=== Updating Next.js Configuration ==="
    local server_hostname=$(hostname -f)
    local server_domain=$(hostname -f | sed 's/^[^.]*\.//')
    
    if [ -n "$server_domain" ] && [ "$server_domain" != "$server_hostname" ]; then
        log_info "Detected domain: $server_domain"
        log_command "sed -i 's|example\.com|$server_domain|g' next.config.js" "Updating domain in next.config.js"
        log_command "sed -i 's|https://example\.com/api/v1|https://$server_domain/api/v1|g' next.config.js" "Updating API URL in next.config.js"
    else
        log_warning "Could not detect domain, using hostname: $server_hostname"
        log_command "sed -i 's|example\.com|$server_hostname|g' next.config.js" "Updating domain in next.config.js"
        log_command "sed -i 's|https://example\.com/api/v1|https://$server_hostname/api/v1|g' next.config.js" "Updating API URL in next.config.js"
    fi
    
    # CRITICAL FIX: Remove output: 'export' to enable server-side rendering
    log_info "=== Removing Static Export Configuration ==="
    log_command "sed -i '/output:/d' next.config.js" "Removing output: 'export' configuration"
    
    # Add CORS headers to Next.js config
    log_info "=== Adding CORS Headers to Next.js Config ==="
    if ! grep -q "async headers()" next.config.js; then
        log_command "sed -i '/module.exports = nextConfig/a\\  async headers() {\\n    return [\\n      {\\n        source: \"\/(.*)\",\\n        headers: [\\n          {\\n            key: \"Access-Control-Allow-Origin\",\\n            value: \"*\",\\n          },\\n          {\\n            key: \"Access-Control-Allow-Methods\",\\n            value: \"GET, POST, PUT, DELETE, OPTIONS\",\\n          },\\n          {\\n            key: \"Access-Control-Allow-Headers\",\\n            value: \"Content-Type, Authorization\",\\n          },\\n        ],\\n      },\\n    ]\\n  },' next.config.js" "Adding CORS headers"
    fi
    
    # Build frontend
    log_info "=== Building Frontend ==="
    if ! npm run build 2>/tmp/frontend_build_error.log; then
        log_error "Frontend build failed"
        cat /tmp/frontend_build_error.log
        exit 1
    fi
    check_status "Frontend build"
    
    # Return to main project directory
    cd /var/www/vendorconnect
    
    # Set proper permissions
    log_info "=== Setting Permissions ==="
    log_command "chown -R www-data:www-data /var/www/vendorconnect" "Setting ownership"
    log_command "chmod -R 755 /var/www/vendorconnect" "Setting base permissions"
    log_command "chmod -R 775 /var/www/vendorconnect/storage /var/www/vendorconnect/bootstrap/cache" "Setting storage permissions"
    
    # Create .env file
    log_info "=== Setting up Environment File ==="
    cd /var/www/vendorconnect
    
    if [ ! -f .env.example ]; then
        log_error ".env.example not found - cannot create .env file"
        log_error "Available files:"
        ls -la >> "$DEBUG_LOG"
        exit 1
    fi
    
    log_command "cp .env.example .env" "Creating .env file"
    
    # Set Laravel environment variables
    log_info "=== Setting Environment Variables ==="
    log_command "sed -i 's/APP_ENV=.*/APP_ENV=production/' .env" "Setting APP_ENV=production"
    log_command "sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env" "Setting APP_DEBUG=false"
    log_command "sed -i 's/LOG_LEVEL=.*/LOG_LEVEL=error/' .env" "Setting LOG_LEVEL=error"
    
    # CRITICAL FIX: Set correct APP_URL based on detected domain
    if [ -n "$server_domain" ] && [ "$server_domain" != "$server_hostname" ]; then
        log_command "sed -i 's|APP_URL=.*|APP_URL=https://$server_domain|' .env" "Setting APP_URL to detected domain"
    else
        log_command "sed -i 's|APP_URL=.*|APP_URL=https://$server_hostname|' .env" "Setting APP_URL to hostname"
    fi
    
    if ! php artisan key:generate; then
        log_error "Laravel key generation failed"
        exit 1
    fi
    check_status "Laravel key generation"
    
    # Configure database
    log_info "=== Configuring Database ==="
    log_command "mysql -u root -e \"CREATE DATABASE IF NOT EXISTS vendorconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"" "Creating database"
    log_command "mysql -u root -e \"CREATE USER IF NOT EXISTS 'vendorconnect_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';\"" "Creating database user"
    log_command "mysql -u root -e \"GRANT ALL PRIVILEGES ON vendorconnect.* TO 'vendorconnect_user'@'localhost';\"" "Granting privileges"
    log_command "mysql -u root -e \"FLUSH PRIVILEGES;\"" "Flushing privileges"
    check_status "Database setup"
    
    # Import initial database schema and admin user
    log_info "=== Setting up Initial Database Schema ==="
    
    if [ -f "vendorconnect-init.sql" ]; then
        log_info "Found vendorconnect-init.sql, importing schema and initial data..."
        if mysql -u root vendorconnect < vendorconnect-init.sql; then
            log_success "Database schema and initial data imported successfully"
            log_info "Database now contains complete schema with admin user"
            log_info "Default admin credentials: admin@vendorconnect.com / password"
            log_info "IMPORTANT: Change the default password after first login!"
        else
            log_error "Failed to import database schema"
            log_info "Will attempt to run Laravel migrations as fallback"
            if ! php artisan migrate --force; then
                log_error "Laravel migrations also failed"
                exit 1
            fi
        fi
    else
        log_warning "vendorconnect-init.sql not found, running Laravel migrations"
        if ! php artisan migrate --force; then
            log_error "Laravel migrations failed"
            exit 1
        fi
        check_status "Laravel migrations"
    fi
    
    # Update .env with database credentials
    log_info "=== Updating Environment File ==="
    log_command "sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env" "Setting DB_HOST"
    log_command "sed -i 's/DB_DATABASE=.*/DB_DATABASE=vendorconnect/' .env" "Setting DB_DATABASE"
    log_command "sed -i 's/DB_USERNAME=.*/DB_USERNAME=vendorconnect_user/' .env" "Setting DB_USERNAME"
    log_command "sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=your_secure_password_here/' .env" "Setting DB_PASSWORD"
    
    # Set up basic .env configuration for production
    log_info "=== Setting up Basic Environment Configuration ==="
    log_info "Note: Configure additional services (mail, pusher, etc.) as needed"
    
    # Set basic production values
    log_command "sed -i 's/APP_NAME=.*/APP_NAME=\"VendorConnect\"/' .env" "Setting APP_NAME"
    log_command "sed -i 's/APP_ENV=.*/APP_ENV=production/' .env" "Setting APP_ENV=production"
    log_command "sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env" "Setting APP_DEBUG=false"
    log_command "sed -i 's/LOG_LEVEL=.*/LOG_LEVEL=error/' .env" "Setting LOG_LEVEL=error"
    
    log_info "Basic environment configuration applied"
    log_info "Configure additional services in .env file as needed for your environment"
    
    # CRITICAL FIX: Add API root route to Laravel
    log_info "=== Adding API Root Route ==="
    # Clear route cache first to ensure new route is recognized
    log_command "php artisan route:clear" "Clearing route cache"
    if ! grep -q "Route::get.*'/'.*function" routes/api.php; then
        log_command "sed -i '/Route::prefix('\''v1'\'')->group(function () {/a\\    Route::get('\''/'\'', function () { return response()->json(['\''message'\'' => '\''VendorConnect API'\'', '\''version'\'' => '\''1.0'\'', '\''status'\'' => '\''API-only mode'\'', '\''documentation'\'' => '\''/api/v1'\'']); });' routes/api.php" "Adding API root route"
    fi
    
    # Run Laravel migrations (only if no SQL dump was imported)
    log_info "=== Running Laravel Migrations ==="
    if ! php artisan migrate --force; then
        log_error "Laravel migrations failed"
        exit 1
    fi
    check_status "Laravel migrations"
    
    # Clear and cache Laravel config
    log_info "=== Caching Laravel Configuration ==="
    log_command "php artisan config:cache" "Caching config"
    log_command "php artisan route:cache" "Caching routes"
    log_command "php artisan view:cache" "Caching views"
    check_status "Laravel caching"
    
    # CRITICAL FIX: Create correct Nginx configuration that matches working production server
    log_info "=== Creating Nginx Configuration ==="
    cat > /etc/nginx/sites-available/vendorconnect << EOF
server {
    listen 80;
    server_name _;
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name _;

    # SSL Configuration (will be configured by Certbot later)
    # ssl_certificate /etc/letsencrypt/live/\$host/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/\$host/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    charset utf-8;

    # API routes - MUST BE FIRST
    location ^~ /api/ {
        root /var/www/vendorconnect/public;
        try_files \$uri /index.php\$is_args\$args;
        
        location ~ \\.php$ {
            fastcgi_pass unix:/var/run/php/php$php_version-fpm.sock;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # Laravel auth routes
    location ~ ^/(forgot-password-mail|reset-password|email|users/authenticate|logout) {
        root /var/www/vendorconnect/public;
        try_files \$uri /index.php\$is_args\$args;
        
        location ~ \\.php$ {
            fastcgi_pass unix:/var/run/php/php$php_version-fpm.sock;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # PHP files in public directory
    location ~ \\.php$ {
        root /var/www/vendorconnect/public;
        fastcgi_pass unix:/var/run/php/php$php_version-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Storage files
    location /storage {
        alias /var/www/vendorconnect/storage/app/public;
        try_files \$uri \$uri/ =404;
    }

    # Next.js static assets - MUST BE BEFORE frontend proxy
    location /_next/static {
        alias /var/www/vendorconnect-frontend/.next/static;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location /_next {
        alias /var/www/vendorconnect-frontend/.next;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Next.js frontend - all other routes (MUST BE LAST)
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}
EOF
    
    # Enable Nginx site
    log_info "=== Configuring Nginx ==="
    log_command "rm -f /etc/nginx/sites-enabled/default" "Removing default site"
    log_command "ln -s /etc/nginx/sites-available/vendorconnect /etc/nginx/sites-enabled/" "Enabling vendorconnect site"
    log_command "nginx -t" "Testing Nginx configuration"
    log_command "systemctl reload nginx" "Reloading Nginx"
    check_status "Nginx configuration"
    
    # Wait for Nginx to be ready
    log_info "=== Waiting for Nginx ==="
    sleep 5
    
    # Start Next.js frontend with PM2
    log_info "=== Starting Next.js Frontend ==="
    cd /var/www/vendorconnect-frontend
    log_command "pm2 start npm --name 'vendorconnect-frontend' -- start --prefix \"/var/www/vendorconnect-frontend\"" "Starting PM2 application"
    log_command "pm2 save" "Saving PM2 configuration"
    log_command "pm2 startup" "Setting up PM2 startup"
    check_status "PM2 startup"
    
    # Wait for PM2 to be ready
    log_info "=== Waiting for PM2 ==="
    sleep 10
    
    # Test the application
    log_info "=== Testing Application ==="
    # Test main application first
    if curl -f http://localhost/ >/dev/null 2>&1; then
        log_success "Main application responding"
        # Then try health endpoint if it exists
        if curl -f http://localhost/health >/dev/null 2>&1; then
            log_success "Health endpoint available"
        else
            log_warning "Health endpoint not available"
        fi
    else
        log_error "Application not responding"
        exit 1
    fi
    
    # Final success logging
    log_success "=== VendorConnect Deployment Completed Successfully ==="
    log_info "Check status: pm2 status"
    log_info "Check logs: tail -f $LOG_FILE"
    log_info "Error logs: tail -f $ERROR_LOG"
    log_info "Debug logs: tail -f $DEBUG_LOG"
    log_info ""
    log_info "IMPORTANT: SSL certificate setup required for production use"
    log_info "Run: certbot --nginx -d your-domain.com"
}

# Run main function
main "$@"

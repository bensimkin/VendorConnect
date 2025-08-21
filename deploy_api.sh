#!/bin/bash
# Deploy API Changes to Live Server

echo "🚀 Deploying API Changes to Live Server..."
echo "==========================================\n"

# Variables
SERVER_HOST="vc.themastermind.com.au"
SERVER_PATH="/var/www/vendorconnect"
BACKUP_DIR="/var/www/backups"

# Create backup
echo "📦 Creating backup..."
ssh root@$SERVER_HOST "mkdir -p $BACKUP_DIR && cp -r $SERVER_PATH $BACKUP_DIR/vendorconnect_backup_$(date +%Y%m%d_%H%M%S)"

# Copy API files to server
echo "📤 Copying API files to server..."

# Copy API routes
scp routes/api.php root@$SERVER_HOST:$SERVER_PATH/routes/

# Copy API controllers
scp -r app/Http/Controllers/Api/ root@$SERVER_HOST:$SERVER_PATH/app/Http/Controllers/

# Copy test files
scp test_api.php root@$SERVER_HOST:$SERVER_PATH/
scp test_live_api.php root@$SERVER_HOST:$SERVER_PATH/

# SSH into server and run deployment commands
echo "🔧 Running deployment commands on server..."
ssh root@$SERVER_HOST << 'EOF'
cd /var/www/vendorconnect

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Regenerate caches
php artisan config:cache
php artisan route:cache

# Set permissions
chown -R www-data:www-data /var/www/vendorconnect
chmod -R 755 /var/www/vendorconnect
chmod -R 775 /var/www/vendorconnect/storage
chmod -R 775 /var/www/vendorconnect/bootstrap/cache

# Restart services
systemctl restart php8.2-fpm
systemctl reload nginx

echo "✅ Deployment completed on server!"
EOF

echo "\n🎉 API Deployment Complete!"
echo "=========================="
echo "The new API endpoints should now be available at:"
echo "https://$SERVER_HOST/api/v1/"
echo "\nYou can now test the API using:"
echo "php test_live_api.php"

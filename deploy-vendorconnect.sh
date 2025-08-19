#!/bin/bash
# VendorConnect Deployment Script

echo "🚀 Deploying VendorConnect..."

# Variables
DOMAIN="vc.themastermind.com.au"
WEB_ROOT="/var/www/vendorconnect"
REPO_URL="https://github.com/bensimkin/VendorConnect.git"
BRANCH="main"  # or "feature/modern-ui-improvements"

# Clone repository
echo "📥 Cloning repository..."
cd /var/www
git clone $REPO_URL vendorconnect
cd vendorconnect

# Checkout desired branch
git checkout $BRANCH

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Copy environment file
echo "⚙️ Setting up environment..."
cp .env.example .env

# Generate application key
php artisan key:generate

# Set permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data $WEB_ROOT
chmod -R 755 $WEB_ROOT
chmod -R 775 $WEB_ROOT/storage
chmod -R 775 $WEB_ROOT/bootstrap/cache

# Install Node dependencies and build assets
echo "📦 Building frontend assets..."
npm install
npm run build

# Setup database
echo "🗄️ Database setup..."
echo "Please update .env with your database credentials, then run:"
echo "php artisan migrate"
echo "php artisan db:seed (if needed)"

# Setup Nginx
echo "🌐 Setting up Nginx..."
cp nginx-vendorconnect.conf /etc/nginx/sites-available/vendorconnect
ln -s /etc/nginx/sites-available/vendorconnect /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment complete!"
echo ""
echo "⚠️ Don't forget to:"
echo "1. Update .env with database credentials"
echo "2. Run database migrations"
echo "3. Setup SSL with: certbot --nginx -d $DOMAIN"
echo "4. Setup cron job for Laravel scheduler"
echo "5. Configure mail settings in .env"

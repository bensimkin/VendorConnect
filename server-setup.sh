#!/bin/bash
# VendorConnect Server Setup Script
# Run this on your Ubuntu/Debian server

echo "🚀 Starting VendorConnect server setup..."

# Update system
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install essential packages
echo "🔧 Installing essential packages..."
apt install -y curl git unzip software-properties-common

# Install Nginx
echo "🌐 Installing Nginx..."
apt install -y nginx

# Install PHP 8.2 and extensions
echo "🐘 Installing PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-json php8.2-curl php8.2-zip php8.2-gd php8.2-redis

# Install MySQL
echo "🗄️ Installing MySQL..."
apt install -y mysql-server
mysql_secure_installation

# Install Redis (optional but recommended)
echo "⚡ Installing Redis..."
apt install -y redis-server

# Install Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Node.js and NPM
echo "📦 Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Create web directory
echo "📁 Setting up web directory..."
mkdir -p /var/www/vendorconnect
chown -R www-data:www-data /var/www/vendorconnect

# Setup firewall
echo "🔒 Configuring firewall..."
ufw allow 22
ufw allow 80
ufw allow 443
ufw --force enable

echo "✅ Basic setup complete!"
echo ""
echo "Next steps:"
echo "1. Configure Nginx site"
echo "2. Setup SSL with Let's Encrypt"
echo "3. Clone and configure VendorConnect"
echo "4. Setup database"

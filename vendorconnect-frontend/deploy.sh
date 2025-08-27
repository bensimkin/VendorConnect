#!/bin/bash

# VendorConnect Frontend Deployment Script

echo "🚀 Starting VendorConnect Frontend Deployment..."

# Configuration
SERVER="root@vc.themastermind.com.au"
REMOTE_PATH="/var/www/vendorconnect-frontend"
LOCAL_PATH="."

# Build the application on the server (local npm may be unavailable)
echo "📦 Building the application on server..."

# Create deployment directory on server
echo "📁 Creating deployment directory on server..."
ssh $SERVER "mkdir -p $REMOTE_PATH"

# Copy files to server
echo "📤 Copying files to server..."
rsync -avz --exclude='node_modules' --exclude='.git' --exclude='.next/cache' \
  $LOCAL_PATH/ $SERVER:$REMOTE_PATH/

# Install dependencies and build on server
echo "📦 Installing dependencies on server..."
ssh $SERVER "cd $REMOTE_PATH && npm ci --omit=dev && npm run build"

# Set up PM2 process
echo "🔧 Setting up PM2 process..."
ssh $SERVER "cd $REMOTE_PATH && pm2 delete vendorconnect-frontend 2>/dev/null || true"
ssh $SERVER "cd $REMOTE_PATH && pm2 start npm --name vendorconnect-frontend -- start"
ssh $SERVER "pm2 save"

# Configure Nginx
echo "🔧 Configuring Nginx..."
ssh $SERVER "cat > /etc/nginx/sites-available/vendorconnect-frontend << 'EOF'
server {
    listen 3000;
    server_name vc.themastermind.com.au;

    location / {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF"

# Enable the site
ssh $SERVER "ln -sf /etc/nginx/sites-available/vendorconnect-frontend /etc/nginx/sites-enabled/"
ssh $SERVER "nginx -t && systemctl reload nginx"

echo "✅ Deployment complete!"
echo "🌐 Frontend should be accessible at https://vc.themastermind.com.au:3000"

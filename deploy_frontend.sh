#!/bin/bash

echo "🚀 DEPLOYING VENDORCONNECT FRONTEND TO SERVER"
echo "=============================================="

# Configuration
SERVER_HOST="vc.themastermind.com.au"
SERVER_USER="root"
REMOTE_DIR="/var/www/vendorconnect-frontend"
LOCAL_BUILD_DIR="./out"

echo ""
echo "📦 Building frontend for production..."

# Build the frontend
npm run build

# Export static files
npx next export

echo ""
echo "📤 Uploading files to server..."

# Create remote directory if it doesn't exist
ssh $SERVER_USER@$SERVER_HOST "mkdir -p $REMOTE_DIR"

# Upload the built files
scp -r $LOCAL_BUILD_DIR/* $SERVER_USER@$SERVER_HOST:$REMOTE_DIR/

echo ""
echo "🔧 Configuring Nginx..."

# Create Nginx configuration for the frontend
ssh $SERVER_USER@$SERVER_HOST "cat > /etc/nginx/sites-available/vendorconnect-frontend << 'EOF'
server {
    listen 80;
    server_name frontend.vc.themastermind.com.au;
    
    root /var/www/vendorconnect-frontend;
    index index.html;
    
    # Handle client-side routing
    location / {
        try_files \$uri \$uri/ /index.html;
    }
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
}
EOF"

# Enable the site
ssh $SERVER_USER@$SERVER_HOST "ln -sf /etc/nginx/sites-available/vendorconnect-frontend /etc/nginx/sites-enabled/"

# Test Nginx configuration
ssh $SERVER_USER@$SERVER_HOST "nginx -t"

# Reload Nginx
ssh $SERVER_USER@$SERVER_HOST "systemctl reload nginx"

echo ""
echo "✅ FRONTEND DEPLOYMENT COMPLETE!"
echo "================================"
echo ""
echo "🌐 Frontend URL: http://frontend.vc.themastermind.com.au"
echo "🔗 API Backend: https://vc.themastermind.com.au/api/v1"
echo ""
echo "📊 Deployment Summary:"
echo "- Built Next.js frontend"
echo "- Uploaded to server"
echo "- Configured Nginx"
echo "- Frontend is now live!"
echo ""
echo "🎉 Your beautiful modern frontend is now deployed!"

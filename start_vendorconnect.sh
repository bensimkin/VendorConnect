#!/bin/bash
# Simple script to start VendorConnect

echo "Starting VendorConnect..."

# Stop any existing PM2 processes
echo "Stopping existing processes..."
pm2 stop vendorconnect-frontend 2>/dev/null
pm2 delete vendorconnect-frontend 2>/dev/null

# Go to frontend directory
cd /var/www/vendorconnect/vendorconnect-frontend

# Check if build exists, if not rebuild
if [ ! -f ".next/BUILD_ID" ]; then
    echo "Build missing, rebuilding..."
    rm -rf .next
    npm run build
fi

# Start with PM2
echo "Starting frontend..."
pm2 start npm --name "vendorconnect-frontend" -- start

# Wait a moment
sleep 5

# Check status
echo ""
echo "Status:"
pm2 list

echo ""
echo "Testing..."
curl -s -o /dev/null -w "VendorConnect Status: %{http_code}\n" https://app.businessnet.com/login

echo ""
echo "VendorConnect started!"

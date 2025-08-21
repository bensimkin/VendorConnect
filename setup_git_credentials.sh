#!/bin/bash

echo "🔧 SETTING UP GIT CREDENTIALS ON SERVER"
echo "========================================"
echo ""

# Check if we're on the server
if [ ! -d "/var/www/vendorconnect" ]; then
    echo "❌ This script should be run on the server!"
    echo "Please SSH into the server and run this script there."
    exit 1
fi

echo "📁 Current directory: $(pwd)"
echo "🐙 Checking git status..."

# Navigate to the project directory
cd /var/www/vendorconnect

echo ""
echo "🔍 Current git remote:"
git remote -v

echo ""
echo "📝 SETTING UP GIT CREDENTIALS"
echo "=============================="
echo ""

# Check if git config is already set
if git config --global user.name > /dev/null 2>&1; then
    echo "✅ Git user.name is already configured: $(git config --global user.name)"
else
    echo "❌ Git user.name is not configured"
    read -p "Enter your git username: " GIT_USERNAME
    git config --global user.name "$GIT_USERNAME"
    echo "✅ Git user.name set to: $GIT_USERNAME"
fi

if git config --global user.email > /dev/null 2>&1; then
    echo "✅ Git user.email is already configured: $(git config --global user.email)"
else
    echo "❌ Git user.email is not configured"
    read -p "Enter your git email: " GIT_EMAIL
    git config --global user.email "$GIT_EMAIL"
    echo "✅ Git user.email set to: $GIT_EMAIL"
fi

echo ""
echo "🔐 SETTING UP GIT CREDENTIALS"
echo "=============================="
echo ""

# Check if credential helper is configured
if git config --global credential.helper > /dev/null 2>&1; then
    echo "✅ Git credential helper is already configured: $(git config --global credential.helper)"
else
    echo "📝 Setting up git credential helper..."
    git config --global credential.helper store
    echo "✅ Git credential helper set to 'store'"
fi

echo ""
echo "🔑 GITHUB PERSONAL ACCESS TOKEN SETUP"
echo "======================================"
echo ""
echo "To push to GitHub, you'll need a Personal Access Token:"
echo ""
echo "1. Go to GitHub.com → Settings → Developer settings → Personal access tokens"
echo "2. Click 'Generate new token (classic)'"
echo "3. Give it a name like 'VendorConnect Server'"
echo "4. Select scopes: 'repo' (full control of private repositories)"
echo "5. Copy the generated token"
echo ""
echo "⚠️  IMPORTANT: Store this token securely - you won't see it again!"
echo ""

read -p "Do you have a GitHub Personal Access Token ready? (y/n): " HAS_TOKEN

if [ "$HAS_TOKEN" = "y" ] || [ "$HAS_TOKEN" = "Y" ]; then
    echo ""
    echo "🔗 TESTING GIT PUSH"
    echo "==================="
    echo ""
    echo "Let's test the git push. When prompted:"
    echo "- Username: Your GitHub username"
    echo "- Password: Your Personal Access Token (not your GitHub password)"
    echo ""
    
    read -p "Press Enter to test git push..."
    
    # Try to push to test credentials
    if git push origin main; then
        echo ""
        echo "✅ SUCCESS! Git credentials are working!"
        echo "You can now push changes directly from the server."
    else
        echo ""
        echo "❌ Git push failed. Please check your credentials."
        echo "You can try again by running: git push origin main"
    fi
else
    echo ""
    echo "📋 SETUP INSTRUCTIONS"
    echo "====================="
    echo ""
    echo "1. Generate a GitHub Personal Access Token:"
    echo "   - Go to GitHub.com → Settings → Developer settings → Personal access tokens"
    echo "   - Click 'Generate new token (classic)'"
    echo "   - Select 'repo' scope"
    echo "   - Copy the token"
    echo ""
    echo "2. Test the setup:"
    echo "   - Run: git push origin main"
    echo "   - Username: Your GitHub username"
    echo "   - Password: Your Personal Access Token"
    echo ""
    echo "3. The credentials will be stored for future use."
    echo ""
fi

echo ""
echo "📊 CURRENT GIT CONFIG"
echo "====================="
echo "User name: $(git config --global user.name)"
echo "User email: $(git config --global user.email)"
echo "Credential helper: $(git config --global credential.helper)"
echo ""

echo "🎉 Git credentials setup complete!"
echo "You can now push changes from the server using: git push origin main"

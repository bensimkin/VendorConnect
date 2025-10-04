# VendorConnect Deployment Script

This repository contains a production-ready deployment script for the VendorConnect application.

## 🚀 Quick Start

1. **Clone the repository** to your target server:
   ```bash
   git clone https://github.com/bensimkin/VendorConnect.git
   cd VendorConnect
   ```

2. **Ensure required files are present**:
   ```bash
   ls -la
   # Should include:
   # - deploy.sh (deployment script)
   # - vendorconnect-init.sql (database schema)
   ```

3. **Make the script executable**:
   ```bash
   chmod +x deploy.sh
   ```

4. **Run the deployment**:
   ```bash
   sudo ./deploy.sh
   ```

## 📋 What Gets Deployed

The script automatically installs and configures:

- **Web Server**: Nginx with proper routing for API and frontend
- **Backend**: Laravel PHP application with all dependencies
- **Frontend**: Next.js application with optimized build
- **Database**: MySQL with proper user setup
- **Cache**: Redis for session and data caching
- **Process Manager**: PM2 for Node.js application management
- **SSL**: Automatic Let's Encrypt certificate setup

## ⚙️ Configuration Options

### Environment Variables

Set these before running the script:

```bash
# Database password (change from default)
export DB_PASSWORD="your-secure-password"
```

### Database Schema

The deployment includes a complete database schema (`vendorconnect-init.sql`) with:
- All essential tables for the application
- Default admin user: `admin@vendorconnect.com` / `password`
- Basic roles (Super Admin, Requester, Tasker) with appropriate permissions
- Default workspace setup

### Customization

The script automatically:
- Detects your server's domain/hostname
- Updates Next.js configuration with correct API URLs
- Configures Nginx for your domain
- Sets up proper SSL certificates

## 🔒 Security Features

- **SSH Key Authentication**: Secure server access
- **Database Isolation**: Separate user with limited privileges
- **Environment Variables**: Secure credential management
- **SSL/TLS**: Automatic HTTPS setup
- **Firewall Ready**: Configured for production use
- **Default Admin Account**: Pre-configured admin user (change password immediately!)

## 📁 Directory Structure

After deployment, your application will be organized as:

```
/var/www/
├── vendorconnect/          # Laravel backend
│   ├── app/
│   ├── routes/
│   ├── storage/
│   └── .env
└── vendorconnect-frontend/ # Next.js frontend
    ├── .next/
    ├── public/
    └── package.json
```

## 🌐 Nginx Configuration

The script creates a comprehensive Nginx configuration that:
- Routes `/api/*` requests to Laravel backend
- Serves Next.js frontend for all other routes
- Handles static assets efficiently
- Includes security headers
- Supports SSL termination

## 🔧 Post-Deployment

After successful deployment:

1. **Change default admin password**:
   ```bash
   # Login with: admin@vendorconnect.com / password
   # Change password immediately through the web interface
   ```

2. **Set up SSL certificates**:
   ```bash
   certbot --nginx -d your-domain.com
   ```

2. **Check application status**:
   ```bash
   pm2 status
   pm2 logs vendorconnect-frontend
   ```

3. **Monitor logs**:
   ```bash
   tail -f /var/log/vendorconnect-deployment.log
   ```

## 🚨 Troubleshooting

### Common Issues

1. **Port conflicts**: Ensure ports 80, 443, and 3000 are available
2. **Memory issues**: The script automatically sets up swap for low-memory servers
3. **Database connection**: Verify MySQL service is running
4. **Frontend not loading**: Check PM2 status and Nginx configuration

### Debug Mode

Enable verbose logging:
```bash
DEBUG=1 sudo ./deploy.sh
```

### Manual Recovery

If deployment fails, you can:
- Check logs in `/var/log/vendorconnect-deployment.log`
- Review Nginx configuration in `/etc/nginx/sites-available/vendorconnect`
- Restart services manually
- Run individual deployment steps from the script

## 📚 Requirements

- **OS**: Ubuntu 20.04 or later
- **RAM**: Minimum 2GB (4GB+ recommended)
- **Storage**: Minimum 20GB available space
- **Access**: Root privileges
- **Network**: Internet connectivity for package installation

## 🔄 Updates

To update an existing deployment:

1. Pull the latest code:
   ```bash
   cd /var/www/vendorconnect
   git pull origin main
   ```

2. Update dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   cd ../vendorconnect-frontend
   npm install
   npm run build
   ```

3. Restart services:
   ```bash
   pm2 restart vendorconnect-frontend
   systemctl reload nginx
   ```

## 📞 Support

For issues or questions:
- Check the deployment logs
- Review the script output for error messages
- Ensure all requirements are met
- Verify network connectivity and permissions

## 📄 License

This deployment script is part of the VendorConnect project and follows the same licensing terms.

---

**Note**: This script is designed for production use but should be reviewed and customized for your specific environment and security requirements.

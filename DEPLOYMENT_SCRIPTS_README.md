# VendorConnect Deployment Scripts

This directory contains comprehensive scripts for deploying and managing the VendorConnect application on production servers.

## 📁 Scripts Overview

### 🚀 Installation Scripts

#### `install_server.sh`
**Purpose**: Complete server installation on a blank Ubuntu/Debian server
**What it does**:
- Installs all required software (Nginx, PHP 8.2, MySQL, Node.js, PM2, Composer)
- Sets up database with secure configuration
- Configures Nginx with security headers
- Deploys the application and builds the frontend
- Sets up SSL with Let's Encrypt (optional)
- Creates comprehensive installation summary

**Usage**:
```bash
# Run on a fresh Ubuntu/Debian server
./install_server.sh
```

**Requirements**:
- Ubuntu 20.04+ or Debian 11+
- User with sudo privileges
- Domain name pointing to the server
- Internet connection

### 🗄️ Database Management Scripts

#### `backup_database.sh`
**Purpose**: Create automated database backups
**What it does**:
- Creates timestamped backup on the server
- Copies backup to local repository
- Commits and pushes to git
- Provides status updates

**Usage**:
```bash
# Create backup and commit to git
./backup_database.sh
```

#### `restore_database.sh`
**Purpose**: Restore database from backup files
**What it does**:
- Lists available backup files
- Drops and recreates database
- Restores from selected backup
- Verifies restoration
- Clears Laravel caches

**Usage**:
```bash
# Interactive restoration
./restore_database.sh

# Restore specific backup file
./restore_database.sh database_backup_20250901_070317.sql
```

### 🔄 Deployment Scripts

#### `rebuild-server.sh`
**Purpose**: Rebuild and restart the application on an existing server
**What it does**:
- Clears Laravel caches
- Updates Composer dependencies
- Runs database migrations
- Builds frontend with Next.js
- Restarts PM2 processes
- Performs health checks

**Usage**:
```bash
# Run on the server
ssh vc-server "cd /var/www/vendorconnect && ./rebuild-server.sh"
```

#### `deploy_api.sh`
**Purpose**: Deploy API changes to production
**What it does**:
- Creates server backup
- Copies API files to server
- Clears and regenerates caches
- Restarts services

**Usage**:
```bash
./deploy_api.sh
```

## 🛠️ Server Setup Process

### 1. Fresh Server Installation

```bash
# 1. Clone the repository on the server
git clone https://github.com/bensimkin/VendorConnect.git /var/www/vendorconnect

# 2. Run the installation script
cd /var/www/vendorconnect
./install_server.sh
```

### 2. Regular Deployments

```bash
# 1. Backup current database
./backup_database.sh

# 2. Pull latest changes
git pull origin main

# 3. Rebuild application
ssh vc-server "cd /var/www/vendorconnect && ./rebuild-server.sh"
```

### 3. Database Restoration

```bash
# Restore from backup
./restore_database.sh database_backup_latest.sql
```

## 📋 Prerequisites

### For Installation Script
- Ubuntu 20.04+ or Debian 11+
- User with sudo privileges
- Domain name configured
- SSH access to server

### For Deployment Scripts
- Existing VendorConnect installation
- SSH key authentication to server
- Git repository access

## 🔧 Configuration

### Environment Variables
The installation script will prompt for:
- MySQL root password
- Domain name
- Application URL
- SSL certificate setup

### Database Configuration
- Database name: `vendorconnect`
- Database user: `vendorconnect`
- Database password: Auto-generated secure password

## 📊 Monitoring and Maintenance

### Service Status
```bash
# Check all services
sudo systemctl status nginx php8.2-fpm mysql

# Check PM2 processes
pm2 status

# View logs
sudo journalctl -u nginx -f
pm2 logs vendorconnect-frontend
```

### Backup Schedule
```bash
# Add to crontab for daily backups
0 2 * * * cd /path/to/VendorConnect && ./backup_database.sh
```

### Health Checks
```bash
# Check application health
curl -I https://yourdomain.com

# Check frontend
curl -I http://localhost:3000
```

## 🔒 Security Considerations

### Post-Installation Security
1. Change default passwords
2. Set up firewall rules
3. Configure fail2ban
4. Enable automatic security updates
5. Set up monitoring and alerting

### Backup Security
- Database backups are stored in git
- Use strong passwords for database access
- Regularly test backup restoration
- Store backups in multiple locations

## 🚨 Troubleshooting

### Common Issues

#### Installation Fails
- Check internet connection
- Verify sudo privileges
- Ensure domain is properly configured
- Check system requirements

#### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database user has proper permissions

#### Frontend Not Loading
- Check PM2 process status
- Verify Node.js version
- Check frontend build logs
- Ensure port 3000 is accessible

#### SSL Certificate Issues
- Verify domain DNS settings
- Check firewall rules
- Ensure port 80/443 are open
- Verify Let's Encrypt rate limits

### Log Locations
- Nginx: `/var/log/nginx/`
- PHP-FPM: `/var/log/php8.2-fpm.log`
- MySQL: `/var/log/mysql/`
- PM2: `pm2 logs`
- Laravel: `storage/logs/`

## 📞 Support

For issues with deployment scripts:
1. Check the troubleshooting section
2. Review log files
3. Verify system requirements
4. Test on a staging environment first

## 🔄 Version History

- **v1.0**: Initial deployment scripts
- **v1.1**: Added database backup/restore functionality
- **v1.2**: Added comprehensive server installation script
- **v1.3**: Added SSL support and security improvements

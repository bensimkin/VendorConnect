# VendorConnect Rebuild Scripts

This directory contains automated rebuild scripts for the VendorConnect application.

## Scripts Overview

### 1. `rebuild.sh` - Local Development Rebuild
**Purpose**: Complete rebuild for local development environment
**Use Case**: When you want to do a clean rebuild of both frontend and backend

**Features**:
- ✅ Laravel cache clearing (all caches)
- ✅ Database migrations
- ✅ Composer dependency updates
- ✅ npm dependency installation (clean install)
- ✅ Frontend build with Next.js
- ✅ Permission setting
- ✅ Health checks

**Usage**:
```bash
./rebuild.sh
```

### 2. `rebuild-server.sh` - Production Server Rebuild
**Purpose**: Optimized rebuild for production server deployment
**Use Case**: When deploying to production server

**Features**:
- ✅ Laravel cache clearing (all caches)
- ✅ Database migrations (forced)
- ✅ Composer dependency updates (production mode)
- ✅ npm dependency installation (production mode)
- ✅ Frontend build with Next.js
- ✅ Permission setting for web server
- ✅ Service restart (PHP-FPM)
- ✅ Comprehensive health checks

**Usage**:
```bash
./rebuild-server.sh
```

## Prerequisites

### For Local Development (`rebuild.sh`):
- PHP 8.0+ with required extensions
- Composer installed
- Node.js 18+ and npm
- MySQL/MariaDB database
- Proper environment configuration

### For Production Server (`rebuild-server.sh`):
- All local prerequisites
- Web server (Apache/Nginx) configured
- PHP-FPM service running
- Proper file permissions
- Database access

## What Each Script Does

### Backend Rebuild Process:
1. **Cache Clearing**: Clears all Laravel caches
   - Application cache
   - Configuration cache
   - Route cache
   - View cache
   - Optimized cache

2. **Database**: Runs all pending migrations

3. **Dependencies**: Updates Composer dependencies
   - Local: Full dependency update
   - Server: Production-optimized install

4. **Permissions**: Sets proper file permissions

### Frontend Rebuild Process:
1. **Cache Clearing**: Clears npm cache

2. **Clean Install**: 
   - Local: Removes node_modules and package-lock.json
   - Server: Uses npm ci for production

3. **Dependencies**: Installs npm dependencies

4. **Build**: Compiles Next.js application

5. **Health Check**: Verifies build success

### Final Steps:
1. **Permissions**: Sets proper ownership and permissions
2. **Cache Clear**: Final cache clearing
3. **Service Restart**: Restarts PHP-FPM (server only)
4. **Health Checks**: Verifies everything is working

## Error Handling

Both scripts include comprehensive error handling:
- **Exit on Error**: Scripts stop on first error
- **Colored Output**: Clear status indicators
- **Health Checks**: Verifies successful completion
- **Detailed Logging**: Shows what's happening at each step

## Troubleshooting

### Common Issues:

1. **Permission Denied**:
   ```bash
   chmod +x rebuild.sh rebuild-server.sh
   ```

2. **Composer Issues**:
   ```bash
   composer clear-cache
   composer install --no-dev
   ```

3. **npm Issues**:
   ```bash
   npm cache clean --force
   rm -rf node_modules package-lock.json
   npm install
   ```

4. **Database Issues**:
   ```bash
   php artisan migrate:status
   php artisan migrate --force
   ```

5. **Cache Issues**:
   ```bash
   php artisan optimize:clear
   ```

## Usage Examples

### Local Development:
```bash
# Make scripts executable
chmod +x rebuild.sh rebuild-server.sh

# Run local rebuild
./rebuild.sh
```

### Server Deployment:
```bash
# SSH to server
ssh vc-server

# Navigate to project
cd /var/www/vendorconnect

# Run server rebuild
./rebuild-server.sh
```

### Quick Cache Clear Only:
```bash
# Just clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## Safety Features

- **Directory Check**: Verifies script is run from correct location
- **Error Exit**: Stops on first error to prevent partial rebuilds
- **Health Checks**: Verifies successful completion
- **Backup Warning**: Reminds to backup before major changes
- **Service Detection**: Automatically detects available services

## Performance Notes

### Local Development:
- Full dependency reinstall for maximum compatibility
- Development dependencies included
- Detailed logging for debugging

### Production Server:
- Production-optimized dependency installation
- Service restart for immediate effect
- Minimal logging for performance
- Optimized autoloader

## Maintenance

These scripts should be updated when:
- New dependencies are added
- Build process changes
- Server configuration changes
- New health checks are needed

## Support

If you encounter issues with these scripts:
1. Check the prerequisites
2. Review the error messages
3. Run individual commands manually
4. Check file permissions
5. Verify environment configuration

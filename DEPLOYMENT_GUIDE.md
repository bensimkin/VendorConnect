# 🚀 VendorConnect API Deployment Guide

## Current Status
- ✅ **Local API**: Complete and tested
- ✅ **Code Cleanup**: Done (removed 41 old controllers)
- ❌ **Live Server**: Still running old version without API

## Deployment Options

### Option 1: Manual File Upload (Recommended)

#### Step 1: Upload API Files
Upload these files to your server at `/var/www/vendorconnect/`:

**API Controllers:**
```
app/Http/Controllers/Api/
├── BaseController.php
├── AuthController.php
├── DashboardController.php
├── TaskController.php
├── ProjectController.php
├── UserController.php
├── ClientController.php
├── StatusController.php
├── PriorityController.php
├── TagController.php
├── TaskTypeController.php
├── UserRoleController.php
├── TaskBriefTemplateController.php
├── TaskBriefQuestionController.php
├── TaskBriefChecklistController.php
├── ProfileController.php
└── NotificationController.php
```

**API Routes:**
```
routes/api.php
```

#### Step 2: Server Commands
SSH into your server and run:

```bash
cd /var/www/vendorconnect

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

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
```

### Option 2: Git Deployment

If your server pulls from Git:

```bash
# On your local machine
git add .
git commit -m "Add complete API with cleanup"
git push origin main

# On server
cd /var/www/vendorconnect
git pull origin main
php artisan config:cache
php artisan route:cache
systemctl restart php8.2-fpm
```

### Option 3: Web-based File Manager

1. Access your server's file manager (cPanel, Plesk, etc.)
2. Navigate to `/var/www/vendorconnect/`
3. Upload the `app/Http/Controllers/Api/` directory
4. Replace `routes/api.php`
5. Run the server commands above

## Testing After Deployment

### Test 1: Check API Routes
```bash
curl -s https://vc.themastermind.com.au/api/v1/auth/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"test","password":"test"}'
```

**Expected Response:** JSON with validation errors (not 404)

### Test 2: Check Authentication
```bash
curl -s https://vc.themastermind.com.au/api/v1/auth/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

**Expected Response:** JSON with user data and token

### Test 3: Check Protected Routes
```bash
# First get token from login, then:
curl -s https://vc.themastermind.com.au/api/v1/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Troubleshooting

### Issue: 404 Errors
**Solution:** Routes not cached properly
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: 500 Errors
**Solution:** Check Laravel logs
```bash
tail -f /var/www/vendorconnect/storage/logs/laravel.log
```

### Issue: Permission Errors
**Solution:** Fix permissions
```bash
chown -R www-data:www-data /var/www/vendorconnect
chmod -R 755 /var/www/vendorconnect
```

### Issue: Class Not Found
**Solution:** Clear autoload cache
```bash
composer dump-autoload
php artisan config:clear
```

## Files to Deploy

### Essential Files:
1. `app/Http/Controllers/Api/` (entire directory)
2. `routes/api.php`

### Optional Files:
3. `API_DOCUMENTATION.md` (for reference)
4. `test_live_api.php` (for testing)

## Verification Checklist

- [ ] API controllers uploaded
- [ ] API routes uploaded
- [ ] Laravel caches cleared
- [ ] Route cache regenerated
- [ ] Permissions set correctly
- [ ] Services restarted
- [ ] API endpoints responding (not 404)
- [ ] Authentication working
- [ ] Protected routes working

## Next Steps After Deployment

1. **Test all API endpoints**
2. **Build new frontend** using the API
3. **Remove old frontend views** after new frontend is working
4. **Monitor API performance**

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check nginx logs: `/var/log/nginx/error.log`
3. Check PHP-FPM logs: `/var/log/php8.2-fpm.log`

---

**The API is ready and waiting to be deployed!** 🚀

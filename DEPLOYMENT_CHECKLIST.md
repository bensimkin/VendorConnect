# VendorConnect Deployment Checklist

## Pre-Deployment
- [ ] Backup current database
- [ ] Backup current application files
- [ ] Test deployment on staging environment (if available)

## Deployment Steps
- [ ] Pull latest changes: `git pull origin main`
- [ ] Install/update dependencies: `composer install --no-dev --optimize-autoloader`
- [ ] Run database migrations: `php artisan migrate --force`
- [ ] Clear and rebuild caches: `php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache`
- [ ] Build frontend: `cd vendorconnect-frontend && npm install && npm run build`
- [ ] Set permissions: `chown -R www-data:www-data /var/www/vendorconnect && chmod -R 755 /var/www/vendorconnect && chmod -R 775 /var/www/vendorconnect/storage /var/www/vendorconnect/bootstrap/cache`
- [ ] Restart services: `systemctl restart php8.3-fpm && systemctl reload nginx && pm2 restart vendorconnect-frontend`

## Post-Deployment Verification

### Environment Variables Check
- [ ] Verify `.env` file exists and contains required variables
- [ ] Check PHP-FPM environment variables are set:
  ```bash
  grep -E "SMART_API_KEY|OPENAI_API_KEY" /etc/php/8.3/fpm/pool.d/www.conf
  ```
- [ ] Test environment variables are loaded in web environment:
  ```bash
  curl -s 'https://app.businessnet.com/api/v1/health/smart-api' | jq .
  ```

### Smart API Health Check
- [ ] Test Smart API can find users:
  ```bash
  curl -s -X POST -H 'Content-Type: application/json' -H 'X-API-Key: vck_IuYqGalsAzWt6TP8y2eg0ZhRj3sJNekU8lonoOtI' -d '{"action":"get_users","params":{}}' 'https://app.businessnet.com/api/v1/smart-task' | jq .
  ```
- [ ] Test Smart API can create tasks:
  ```bash
  curl -s -X POST -H 'Content-Type: application/json' -H 'X-API-Key: vck_IuYqGalsAzWt6TP8y2eg0ZhRj3sJNekU8lonoOtI' -d '{"action":"create_task","params":{"title":"Deployment Test","assigned_to":"Kristine"}}' 'https://app.businessnet.com/api/v1/smart-task' | jq .
  ```

### General Application Health
- [ ] Test main application loads: `curl -s https://app.businessnet.com/ | head -20`
- [ ] Test API endpoints respond: `curl -s https://app.businessnet.com/api/v1/ | jq .`
- [ ] Check application logs for errors: `tail -n 50 /var/www/vendorconnect/storage/logs/laravel.log`

## Critical Environment Variables
The following environment variables must be properly configured:

### Required in .env file:
- `SMART_API_KEY=your_smart_api_key_here`
- `OPENAI_API_KEY=your_openai_api_key_here`

### Required in PHP-FPM configuration:
- `env[SMART_API_KEY] = your_smart_api_key_here`
- `env[OPENAI_API_KEY] = your_openai_api_key_here`

## Troubleshooting

### If Smart API returns "No users found":
1. Check environment variables are loaded: `curl -s 'https://app.businessnet.com/api/v1/health/smart-api' | jq .`
2. Verify PHP-FPM configuration: `grep -E "SMART_API_KEY|OPENAI_API_KEY" /etc/php/8.3/fpm/pool.d/www.conf`
3. Restart PHP-FPM: `systemctl restart php8.3-fpm`
4. Check application logs: `tail -n 50 /var/www/vendorconnect/storage/logs/laravel.log`

### If environment variables are missing:
1. Add to PHP-FPM config: `echo 'env[SMART_API_KEY] = vck_IuYqGalsAzWt6TP8y2eg0ZhRj3sJNekU8lonoOtI' >> /etc/php/8.3/fpm/pool.d/www.conf`
2. Restart PHP-FPM: `systemctl restart php8.3-fpm`
3. Verify: `curl -s 'https://app.businessnet.com/api/v1/health/smart-api' | jq .`

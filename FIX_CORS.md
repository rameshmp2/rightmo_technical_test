# CORS Error Fix for Docker Setup

## Root Cause

The CORS error occurred because **Nginx** (the web server in front of the Laravel application) was not configured to handle CORS headers. When running in Docker, Nginx needs to explicitly add CORS headers to allow requests from the Next.js frontend.

## Changes Made

I've fixed the CORS error by making the following changes:

### 1. Updated `backend/docker/nginx/conf.d/app.conf` ⭐ (MOST IMPORTANT)
Added CORS headers to Nginx configuration:
- Allow requests from http://localhost:3000
- Handle preflight OPTIONS requests
- Set proper CORS headers for all responses

### 2. Updated `backend/bootstrap/app.php`
Added HandleCors middleware to the API middleware stack.

### 3. Updated `backend/.env`
Added Sanctum configuration:
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

### 4. Updated `backend/config/cors.php`
Changed from wildcard to specific allowed origins:
```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
],
```

## Steps to Apply the Fix

Run these commands in your terminal from the project root:

```bash
# Stop all containers
docker-compose down

# Rebuild and start containers (this will reload Nginx config)
docker-compose up -d --build

# Clear Laravel cache (note: container name is 'laravel-app' not 'backend')
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

# Verify containers are running
docker-compose ps
```

## Verify the Fix

1. Open your browser to http://localhost:3000
2. Open browser DevTools (F12) and check the Network tab
3. Try to login with:
   - Email: `test@example.com`
   - Password: `password123`
4. The CORS error should now be resolved
5. You should see the response headers include:
   - `Access-Control-Allow-Origin: http://localhost:3000`
   - `Access-Control-Allow-Credentials: true`

## If Still Getting CORS Error

### Option 1: Check Nginx Container Logs
```bash
# Check nginx logs for errors
docker-compose logs nginx

# Check if nginx reloaded the config
docker-compose exec nginx nginx -t
```

### Option 2: Restart Nginx Container
```bash
# Restart just the nginx container
docker-compose restart nginx

# Or restart all containers
docker-compose restart
```

### Option 3: Complete Fresh Start
```bash
# Stop everything and remove volumes
docker-compose down -v

# Rebuild and start
docker-compose up -d --build

# Run migrations and seeders again
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan storage:link
```

### Option 4: Check Container Names
```bash
# List all running containers
docker-compose ps

# You should see:
# - laravel-app (PHP-FPM)
# - laravel-nginx (Nginx)
# - laravel-db (MariaDB)
# - laravel-phpmyadmin (PhpMyAdmin)
# - nextjs-frontend (Next.js)
```

### Option 5: Manual Nginx Config Verification
```bash
# Enter the nginx container
docker-compose exec nginx sh

# Check if the config file is correct
cat /etc/nginx/conf.d/app.conf

# You should see the CORS headers in the output
# Exit the container
exit
```

## Why This Happened

When running Laravel in Docker with Nginx as a reverse proxy:

1. **Nginx sits in front of Laravel** - All HTTP requests go through Nginx first
2. **Nginx needs to handle CORS** - CORS headers must be set at the Nginx level, not just in Laravel
3. **Preflight OPTIONS requests** - Browsers send OPTIONS requests before POST/PUT/DELETE, which Nginx must handle
4. **Laravel's CORS config alone isn't enough** - Because Nginx is the web server, it controls the HTTP response headers

The fix adds CORS headers directly to the Nginx configuration so that all responses include the necessary headers for cross-origin requests.

## Testing CORS Headers

You can test if CORS headers are being sent using curl:

```bash
# Test OPTIONS request (preflight)
curl -X OPTIONS http://localhost:8000/api/login \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v

# You should see these headers in the response:
# Access-Control-Allow-Origin: http://localhost:3000
# Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
# Access-Control-Allow-Credentials: true
```

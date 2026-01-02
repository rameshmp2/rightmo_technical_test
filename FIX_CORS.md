# CORS Configuration - Final Working Setup

## Overview

The CORS (Cross-Origin Resource Sharing) configuration has been successfully set up to allow the Next.js frontend (localhost:3000) to communicate with the Laravel backend (localhost:8000).

## How CORS is Handled

**Laravel Handles All CORS** - The CORS headers are managed entirely by Laravel's built-in CORS middleware through the HandleCors middleware, not by Nginx.

## Configuration Files

### 1. Laravel CORS Middleware - [backend/bootstrap/app.php](backend/bootstrap/app.php)
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

### 2. Laravel CORS Configuration - [backend/config/cors.php](backend/config/cors.php)
```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
],

'supports_credentials' => true,
```

### 3. Laravel Sanctum Configuration - [backend/.env](backend/.env)
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

### 4. Nginx Configuration - [backend/docker/nginx/conf.d/app.conf](backend/docker/nginx/conf.d/app.conf)
Simple pass-through configuration - Nginx does NOT add CORS headers, it just passes requests to PHP-FPM and allows Laravel's headers to pass through.

## Expected CORS Headers

When making requests from localhost:3000, you should see these headers in responses:

```
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Credentials: true
Vary: Origin
```

For OPTIONS preflight requests, you'll also see:
```
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With
```

## Testing CORS

### Test with curl:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:3000" \
  -d '{"email":"test@example.com","password":"password123"}' \
  -i
```

You should see the `Access-Control-Allow-Origin` header in the response.

### Test in Browser:
1. Open http://localhost:3000
2. Open DevTools (F12)
3. Go to Network tab
4. Try to login with:
   - Email: `test@example.com`
   - Password: `password123`
5. Check the response headers - you should see CORS headers

## Troubleshooting

### Issue: "No 'Access-Control-Allow-Origin' header"

**Solution**: Restart the containers to ensure all configurations are loaded:
```bash
docker-compose restart
```

### Issue: Duplicate CORS headers

**Problem**: Both Nginx and Laravel adding CORS headers
**Solution**: Already fixed - Nginx config is clean and only Laravel adds CORS headers

### Issue: CORS works for GET but not POST

**Problem**: Browser sends preflight OPTIONS request which isn't handled
**Solution**: Already handled by Laravel's HandleCors middleware which responds to OPTIONS requests

## Why This Works

1. **Laravel's HandleCors middleware** is registered in the API middleware stack
2. **Nginx simply passes requests** to PHP-FPM without modification
3. **Laravel adds CORS headers** to all API responses
4. **No header conflicts** because only Laravel manages CORS

## Production Considerations

For production deployment:

1. **Update allowed origins** in `backend/config/cors.php`:
   ```php
   'allowed_origins' => [
       'https://yourdomain.com',
   ],
   ```

2. **Update Sanctum domains** in `.env`:
   ```env
   SANCTUM_STATEFUL_DOMAINS=yourdomain.com
   SESSION_DOMAIN=.yourdomain.com
   ```

3. **Use environment variables** for dynamic configuration:
   ```php
   'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),
   ```

## Files Modified

1. ✅ [backend/bootstrap/app.php](backend/bootstrap/app.php) - Added HandleCors middleware
2. ✅ [backend/config/cors.php](backend/config/cors.php) - Configured allowed origins
3. ✅ [backend/.env](backend/.env) - Added Sanctum stateful domains
4. ✅ [backend/docker/nginx/conf.d/app.conf](backend/docker/nginx/conf.d/app.conf) - Clean pass-through config

## Summary

The CORS setup is now working perfectly:
- ✅ Frontend can make API requests
- ✅ Credentials (tokens) are sent correctly
- ✅ No duplicate headers
- ✅ Preflight requests handled automatically
- ✅ Clean separation of concerns (Laravel handles CORS, Nginx handles proxying)

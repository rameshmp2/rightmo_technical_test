# Docker Automation Setup

## Overview

The Docker setup has been fully automated to provide a zero-configuration experience. When you run `docker-compose up -d`, everything is set up automatically.

## What Happens Automatically

### 1. Database Health Check
The Docker setup includes a health check for the MariaDB database that:
- Checks if the database is ready every 5 seconds
- Retries up to 10 times
- Ensures the database is fully initialized before Laravel starts

### 2. Automatic Laravel Setup (via Entrypoint Script)

The Laravel container runs an entrypoint script ([backend/docker-entrypoint.sh](backend/docker-entrypoint.sh)) that automatically:

1. **Waits for Database** - Uses `php artisan db:show` to verify database connection
2. **Runs Migrations** - Executes `php artisan migrate --seed --force`
3. **Seeds Database** - Populates database with test users and sample products
4. **Creates Storage Link** - Runs `php artisan storage:link` for image uploads
5. **Optimizes Application** - Clears configuration, cache, and route cache
6. **Starts PHP-FPM** - Launches the PHP application server

## Modified Files

### 1. [backend/docker-entrypoint.sh](backend/docker-entrypoint.sh)
A bash script that runs all setup tasks before starting PHP-FPM.

**Key Features:**
- Database connection retry logic
- Automatic error detection (`set -e`)
- Clear progress messages
- Graceful handling of storage link (may already exist)

### 2. [backend/Dockerfile](backend/Dockerfile)
Updated to:
- Copy the entrypoint script into the container
- Make it executable
- Set it as the container's ENTRYPOINT

### 3. [docker-compose.yml](docker-compose.yml)
Updated to:
- Add health check to database service
- Use `depends_on` with `condition: service_healthy` for the app service
- Ensure app container waits for database to be ready

### 4. [README.md](README.md)
Updated to:
- Remove manual migration/seeding steps
- Highlight automatic setup
- Simplify quick start to just 2 main steps

## Benefits

### For Fresh Installations
```bash
# Just one command needed!
docker-compose up -d

# Everything is ready immediately:
# ✓ Database created and migrated
# ✓ Test users seeded
# ✓ Sample products added
# ✓ Storage link created
# ✓ Application optimized
```

### For Development
The entrypoint script runs every time the container starts, so:
- Fresh database after `docker-compose down -v && docker-compose up -d`
- Automatic migrations when adding new migration files
- No manual intervention needed

### For Production
You can modify the entrypoint script to skip seeding in production:
```bash
# Only run migrations, skip seeding
php artisan migrate --force
```

## Troubleshooting

### View Setup Progress
```bash
# Watch the Laravel container logs to see setup progress
docker-compose logs -f app

# You should see:
# Starting Laravel application setup...
# Waiting for database connection...
# Database is ready!
# Running database migrations and seeders...
# Creating storage link...
# Optimizing application...
# Laravel setup completed successfully!
# Starting PHP-FPM...
```

### Database Connection Issues
If the entrypoint script can't connect to the database:
```bash
# Check database container health
docker-compose ps db

# Check database logs
docker-compose logs db

# Restart if needed
docker-compose restart db
```

### Re-run Setup Manually
If you need to manually re-run migrations:
```bash
# Enter the container
docker-compose exec app bash

# Run migrations again
php artisan migrate:fresh --seed
php artisan storage:link
```

### Disable Automatic Migrations
To disable automatic migrations, you can:

**Option 1: Comment out in entrypoint script**
Edit `backend/docker-entrypoint.sh` and comment out the migration line:
```bash
# php artisan migrate --seed --force
```

**Option 2: Override entrypoint in docker-compose.yml**
```yaml
app:
  # ... other config
  entrypoint: ["php-fpm"]  # Skip entrypoint script
```

## Comparison: Before vs After

### Before (Manual Setup)
```bash
docker-compose up -d
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan storage:link
```

### After (Automated Setup)
```bash
docker-compose up -d
# Done! Application ready to use
```

## Technical Details

### Database Health Check Configuration
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-proot"]
  interval: 5s
  timeout: 5s
  retries: 10
```

This ensures:
- Database is checked every 5 seconds
- Each check times out after 5 seconds
- Up to 10 retries (50 seconds total)
- Laravel waits for healthy status before starting

### Dependency Management
```yaml
app:
  depends_on:
    db:
      condition: service_healthy
```

This ensures:
- App container waits for db container
- App only starts when db health check passes
- Prevents "connection refused" errors
- Proper startup order

## Security Considerations

### Production Deployment
For production, you should:

1. **Remove `--seed` flag** from entrypoint script:
   ```bash
   php artisan migrate --force  # No --seed
   ```

2. **Use environment variable** to control seeding:
   ```bash
   if [ "$APP_ENV" != "production" ]; then
     php artisan migrate --seed --force
   else
     php artisan migrate --force
   fi
   ```

3. **Secure database credentials** in `.env` file

### Development Safety
The current setup with `--seed` is safe for development because:
- Uses local Docker containers
- Isolated from production
- Easy to reset with `docker-compose down -v`

## Future Enhancements

Possible improvements:
- [ ] Add environment-based conditional seeding
- [ ] Add Laravel queue worker startup
- [ ] Add Laravel scheduler (cron) setup
- [ ] Add Redis for caching and sessions
- [ ] Add automatic SSL certificate generation

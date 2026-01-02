# Database Migration Guide

## Automatic vs Manual Migrations

### Automatic Migrations (Default Behavior)

The Docker setup is configured to **automatically run migrations** on first startup. This happens via the `backend/docker-entrypoint.sh` script.

**When automatic migrations run:**
- ✅ First time starting containers (fresh database)
- ✅ When `migrations` table doesn't exist in the database
- ✅ After `docker-compose down -v` (volumes deleted)

**When automatic migrations DON'T run:**
- ❌ Container restart (data already exists)
- ❌ Container rebuild with `--build` (preserves existing data)
- ❌ When `migrations` table already exists

---

## Manual Migration Commands

If you need to run migrations manually or the automatic process isn't working, use these commands:

### 1. Run Fresh Migrations (Drops All Tables)

**⚠️ WARNING: This deletes ALL existing data!**

```bash
# Using Docker
docker-compose exec app php artisan migrate:fresh --seed

# If not using Docker
php artisan migrate:fresh --seed
```

**What this does:**
1. Drops all tables
2. Runs all migrations from scratch
3. Seeds the database with test data

### 2. Run Pending Migrations Only

```bash
# Using Docker
docker-compose exec app php artisan migrate --seed

# If not using Docker
php artisan migrate --seed
```

**What this does:**
1. Runs only migrations that haven't been run yet
2. Seeds the database with test data
3. Preserves existing data

### 3. Run Migrations Without Seeding

```bash
# Using Docker
docker-compose exec app php artisan migrate

# If not using Docker
php artisan migrate
```

**What this does:**
1. Runs only the migrations
2. Does NOT seed any data

### 4. Rollback Last Migration Batch

```bash
# Using Docker
docker-compose exec app php artisan migrate:rollback

# If not using Docker
php artisan migrate:rollback
```

**What this does:**
1. Rolls back the last batch of migrations
2. Useful for undoing mistakes

### 5. Check Migration Status

```bash
# Using Docker
docker-compose exec app php artisan migrate:status

# If not using Docker
php artisan migrate:status
```

**What this does:**
1. Shows which migrations have run
2. Shows which migrations are pending

---

## Seeder Commands

### Run All Seeders

```bash
# Using Docker
docker-compose exec app php artisan db:seed

# If not using Docker
php artisan db:seed
```

### Run Specific Seeder

```bash
# Using Docker
docker-compose exec app php artisan db:seed --class=ProductSeeder
docker-compose exec app php artisan db:seed --class=UserSeeder

# If not using Docker
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=UserSeeder
```

---

## Complete Fresh Setup (Step by Step)

If automatic migrations aren't working and you want to start completely fresh:

### Step 1: Stop and Remove Everything

```bash
# Stop all containers
docker-compose down

# Remove all volumes (deletes database data)
docker-compose down -v

# Remove any leftover volumes manually
docker volume ls | grep technical_test
docker volume rm technical_test_dbdata
```

### Step 2: Clean Old Containers (Optional)

```bash
# Remove old containers
docker rm -f laravel-app laravel-db laravel-nginx laravel-phpmyadmin nextjs-frontend

# Remove old images (optional, forces rebuild)
docker rmi technical_test-app technical_test-frontend
```

### Step 3: Rebuild and Start

```bash
# Rebuild images and start containers
docker-compose up -d --build
```

### Step 4: Wait for Setup (70 seconds)

```bash
# Wait for automatic setup to complete
sleep 70
```

### Step 5: Check Logs

```bash
# Check if migrations ran
docker logs laravel-app | grep -E "migrations|seeding"
```

**Expected output:**
```
Running database migrations...
INFO  Preparing database.
Creating migration table ..................................... DONE
INFO  Running migrations.
0001_01_01_000000_create_users_table ......................... DONE
0001_01_01_000001_create_cache_table ......................... DONE
... (all migrations)
Running database seeders...
INFO  Seeding database.
Database\Seeders\UserSeeder .................................. DONE
Database\Seeders\ProductSeeder ................................ DONE
```

### Step 6: Verify Database

```bash
# Check if data was seeded
docker-compose exec app php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Categories: ' . App\Models\Category::count() . PHP_EOL;
echo 'Products: ' . App\Models\Product::count() . PHP_EOL;
"
```

**Expected output:**
```
Users: 2
Categories: 4
Products: 20
```

---

## Force Run Migrations Manually

If automatic migrations didn't run for some reason, force them manually:

### Option 1: Run Inside Container

```bash
# Enter the container
docker-compose exec app bash

# Inside container, run:
php artisan migrate:fresh --seed

# Exit container
exit
```

### Option 2: Direct Command

```bash
# Single command from host
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Troubleshooting Migration Issues

### Issue 1: "Database doesn't exist"

**Error:**
```
SQLSTATE[HY000] [1049] Unknown database 'technical_test'
```

**Solution:**
```bash
# Create database manually
docker-compose exec db mysql -uroot -proot -e "CREATE DATABASE technical_test;"

# Then run migrations
docker-compose exec app php artisan migrate --seed
```

### Issue 2: "Migrations table not found" but migrations don't run

**Solution:**
```bash
# Force run migrations
docker-compose exec app php artisan migrate:install
docker-compose exec app php artisan migrate --seed
```

### Issue 3: "Column not found: category"

This means the ProductSeeder still has old code using `category` instead of `category_id`.

**Solution:**
```bash
# Rebuild the Docker image to include latest code
docker-compose up -d --build app

# Then force fresh migrations
docker-compose exec app php artisan migrate:fresh --seed
```

### Issue 4: Migrations run but no data seeded

**Solution:**
```bash
# Run seeders manually
docker-compose exec app php artisan db:seed --force
```

### Issue 5: "Public/storage link already exists"

This is just a warning, not an error. Safe to ignore.

**To fix warning (optional):**
```bash
docker-compose exec app rm -f public/storage
docker-compose exec app php artisan storage:link
```

### Issue 6: "vendor/autoload.php not found"

**Error:**
```
Fatal error: Failed opening required '/var/www/vendor/autoload.php'
```

**Cause:** Composer dependencies weren't installed during Docker build.

**Solution:**
```bash
# Rebuild with --build flag to ensure composer install runs
docker-compose down
docker-compose up -d --build

# Wait for setup
sleep 70

# Verify it worked
docker-compose exec app php artisan --version
```

**Root cause fixed:** The Dockerfile now includes `RUN composer install` to ensure dependencies are always installed during build.

### Issue 7: Container keeps restarting

**Check logs:**
```bash
docker logs laravel-app
```

**Common causes:**
- Database not ready (wait 10-20 seconds)
- Syntax error in entrypoint script
- PHP-FPM not starting
- Missing composer dependencies (see Issue 6)

---

## Database Commands Reference

### Access MySQL CLI

```bash
# Method 1: Through Laravel container
docker-compose exec app mysql -h db -u root -proot technical_test

# Method 2: Direct to database container
docker-compose exec db mysql -uroot -proot technical_test
```

### Check Tables

```sql
-- Show all tables
SHOW TABLES;

-- Check migrations table
SELECT * FROM migrations;

-- Check products count
SELECT COUNT(*) FROM products;

-- Check categories count
SELECT COUNT(*) FROM categories;
```

### Drop Database

```bash
# Drop database (careful!)
docker-compose exec db mysql -uroot -proot -e "DROP DATABASE IF EXISTS technical_test;"

# Recreate database
docker-compose exec db mysql -uroot -proot -e "CREATE DATABASE technical_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Quick Commands Cheat Sheet

```bash
# Fresh start with everything
docker-compose down -v && docker-compose up -d --build

# Run fresh migrations
docker-compose exec app php artisan migrate:fresh --seed

# Check migration status
docker-compose exec app php artisan migrate:status

# Run seeders only
docker-compose exec app php artisan db:seed

# Check database data
docker-compose exec app php artisan tinker --execute="echo App\Models\Product::count();"

# View logs
docker logs laravel-app | tail -50

# Access database
docker-compose exec app mysql -h db -u root -proot technical_test
```

---

## Understanding the Entrypoint Script

The automatic migration process is controlled by `backend/docker-entrypoint.sh`:

### How it works:

1. **Wait 10 seconds** for database server to start
2. **Check if database exists** using mysql command
3. **Check if migrations table exists** using tinker
4. **If migrations table NOT found:**
   - Run `php artisan migrate --force`
   - Run `php artisan db:seed --force`
   - Create storage link
5. **If migrations table found:**
   - Skip migrations (data already exists)
   - Just ensure storage link exists
6. **Clear caches**
7. **Start PHP-FPM**

### View the script:

```bash
# View entrypoint script
cat backend/docker-entrypoint.sh

# Or view inside container
docker-compose exec app cat /usr/local/bin/docker-entrypoint.sh
```

---

## Testing That Migrations Work

### Complete Test Procedure:

```bash
# 1. Clean slate
docker-compose down -v
docker volume ls | grep technical_test | awk '{print $2}' | xargs docker volume rm 2>/dev/null || true

# 2. Start fresh
docker-compose up -d --build

# 3. Wait for setup
echo "Waiting for migrations to complete (70 seconds)..."
sleep 70

# 4. Check logs
echo "=== CHECKING LOGS ==="
docker logs laravel-app | grep -A5 "Running database migrations"

# 5. Verify data
echo "=== VERIFYING DATA ==="
docker-compose exec app php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count();
echo 'Categories: ' . App\Models\Category::count();
echo 'Products: ' . App\Models\Product::count();
"

# 6. Test API
echo "=== TESTING API ==="
curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}' | grep -o "token"
```

**If you see:**
- ✅ Migrations: 8 DONE messages
- ✅ Seeding: 2 DONE messages
- ✅ Users: 2, Categories: 4, Products: 20
- ✅ "token" in API response

**Then migrations ARE working!**

---

## When Migrations Don't Run Automatically

If automatic migrations aren't running after fresh install:

### Diagnostic Steps:

```bash
# 1. Check if container is running
docker-compose ps app

# 2. Check container logs
docker logs laravel-app

# 3. Check if entrypoint script exists
docker-compose exec app ls -la /usr/local/bin/docker-entrypoint.sh

# 4. Check if script is executable
docker-compose exec app cat /usr/local/bin/docker-entrypoint.sh | head -20

# 5. Check database connection
docker-compose exec app php artisan tinker --execute="echo DB::connection()->getPdo();"
```

### Manual Migration Process:

If automatic doesn't work, run manually:

```bash
# 1. Create database if needed
docker-compose exec db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS technical_test;"

# 2. Run migrations
docker-compose exec app php artisan migrate:fresh --seed --force

# 3. Create storage link
docker-compose exec app php artisan storage:link

# 4. Verify
docker-compose exec app php artisan tinker --execute="echo App\Models\Product::count();"
```

---

## Production Deployment

For production, disable automatic seeding:

### Edit entrypoint script:

Change this line in `backend/docker-entrypoint.sh`:
```bash
# FROM:
php artisan db:seed --force 2>&1

# TO:
if [ "$APP_ENV" != "production" ]; then
    php artisan db:seed --force 2>&1
fi
```

Then in production `.env`:
```env
APP_ENV=production
```

This ensures seeders only run in development/staging environments.

---

## Summary

**Automatic migrations should work out of the box!**

If they don't:
1. Check logs: `docker logs laravel-app`
2. Wait 70 seconds for setup to complete
3. Rebuild: `docker-compose up -d --build`
4. Manual fallback: `docker-compose exec app php artisan migrate:fresh --seed`

**Most common issue:** Not waiting long enough for the setup to complete!

# Vendor Directory Missing Fix

## Issue Summary

**Problem:** `vendor/autoload.php` not found error when running Laravel commands inside Docker container.

**Error Message:**
```
Warning: require(/var/www/vendor/autoload.php): Failed to open stream: No such file or directory
Fatal error: Failed opening required '/var/www/vendor/autoload.php'
```

## Root Cause

The issue had **TWO parts**:

### Part 1: Dockerfile Missing Composer Install
The Dockerfile was copying files but never running `composer install` to download PHP dependencies.

### Part 2: Volume Mount Overwriting Vendor Directory
Even after adding `composer install` to the Dockerfile, the vendor directory was being overwritten at runtime because:

1. Dockerfile builds image and runs `composer install` → vendor directory exists in image
2. docker-compose.yml mounts `./backend:/var/www` as a volume
3. This mount **replaces** the container's `/var/www` with the local `./backend` folder
4. Local `./backend` folder doesn't have `vendor/` (it's in `.gitignore`)
5. Result: vendor directory disappears when container starts

## Complete Fix

### Step 1: Add Composer Install to Dockerfile

**File:** `backend/Dockerfile` (Line 34)

```dockerfile
# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader
```

**Why removed `--no-dev`:** Development dependencies (like `laravel/tinker`, `phpunit`, etc.) are needed for the entrypoint script and testing.

### Step 2: Add Anonymous Volume to docker-compose.yml

**File:** `docker-compose.yml` (Line 17)

```yaml
services:
  app:
    volumes:
      - ./backend:/var/www
      - /var/www/vendor  # ← Added this line
      - ./backend/docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
```

**What this does:** Tells Docker to preserve the `/var/www/vendor` directory from the built image instead of replacing it with the (non-existent) local one.

## How to Apply the Fix

### For Existing Installations:

```bash
# 1. Stop containers
docker-compose down

# 2. Rebuild with the fixed Dockerfile and docker-compose.yml
docker-compose up -d --build

# 3. Wait for setup (70 seconds)
sleep 70

# 4. Verify vendor directory exists
docker exec laravel-app test -d /var/www/vendor && echo "✓ Fixed!" || echo "✗ Still broken"

# 5. Verify Laravel works
docker exec laravel-app php artisan --version
```

**Expected output:**
```
✓ Fixed!
Laravel Framework 12.44.0
```

### For Fresh Installations:

The fix is already in place! Just run:
```bash
docker-compose up -d
```

And everything will work automatically.

## Manual Workaround (If Fix Doesn't Work)

If you still get the error, manually install dependencies inside the container:

```bash
# Install composer dependencies inside running container
docker exec laravel-app composer install --no-interaction --optimize-autoloader

# Verify it worked
docker exec laravel-app php artisan --version
```

## Understanding Anonymous Volumes

### Without Anonymous Volume:
```yaml
volumes:
  - ./backend:/var/www  # Mounts entire local backend folder
```
**Result:** Local folder (without vendor) → overwrites → container's folder (with vendor) → vendor disappears

### With Anonymous Volume:
```yaml
volumes:
  - ./backend:/var/www
  - /var/www/vendor  # Preserves vendor from built image
```
**Result:** Local folder mounts EXCEPT vendor subfolder → vendor directory preserved from Docker build

## Why This Pattern Is Common

This is the standard Docker development pattern for:
- **PHP Projects:** Preserve `vendor/` directory
- **Node.js Projects:** Preserve `node_modules/` directory
- **Python Projects:** Preserve virtual environments

**Goal:** Mount source code for live editing while preserving installed dependencies.

## Verification Commands

### Check if vendor directory exists:
```bash
docker exec laravel-app ls -la /var/www/vendor | head -10
```

### Check vendor contents:
```bash
docker exec laravel-app ls /var/www/vendor | wc -l
```
**Expected:** 40+ directories (composer packages)

### Test Laravel commands:
```bash
# Should show Laravel version
docker exec laravel-app php artisan --version

# Should show migration status
docker exec laravel-app php artisan migrate:status

# Should show database counts
docker exec laravel-app php artisan tinker --execute="echo App\\Models\\Product::count();"
```

## Related Files

- [DOCKERFILE_FIX.md](DOCKERFILE_FIX.md) - Original composer install fix
- [FINAL_SETUP_SUMMARY.md](FINAL_SETUP_SUMMARY.md) - Complete setup documentation
- [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) - Includes troubleshooting for this error

## Technical Details

### .dockerignore
The `backend/.dockerignore` should include:
```
vendor/
node_modules/
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
```

This prevents copying vendor from host during build (we want to install it fresh).

### Volume Mount Order Matters

Docker processes volumes in order:
1. First: `./backend:/var/www` mounts entire folder
2. Then: `/var/www/vendor` overrides the vendor subfolder

Without the second line, the first mount would include an empty vendor directory from the host.

## Summary

**Problem:** vendor/autoload.php not found
**Cause 1:** Dockerfile didn't run composer install
**Cause 2:** Volume mount overwrote vendor directory
**Solution 1:** Add `RUN composer install` to Dockerfile
**Solution 2:** Add `/var/www/vendor` anonymous volume to docker-compose.yml
**Result:** vendor directory preserved, all Laravel commands work correctly

✅ **Status:** FIXED and tested as of 2026-01-02

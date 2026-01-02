# Fresh Install Analysis - Database Migration Process

## Test Date: 2026-01-02

## Test Scenario: Complete Fresh Installation
Simulated fresh install by removing all volumes and containers:
```bash
docker-compose down -v
docker volume rm rightmo_technical_test_dbdata
docker-compose up -d
```

## What Happens During Fresh Install

### 1. Container Startup Sequence

```
✓ Network created: technical_test_fullstack-network
✓ Volume created: technical_test_dbdata (empty)
✓ Database container started: laravel-db (MariaDB 10.4)
✓ Frontend container started: nextjs-frontend
✓ PhpMyAdmin container started: laravel-phpmyadmin
✓ App container started: laravel-app (waits for DB health check)
```

### 2. Laravel Entrypoint Script Execution

The `backend/docker-entrypoint.sh` script runs automatically when the app container starts:

#### Step 1: Wait for Database Server (10 seconds)
```
Starting Laravel application setup...
Waiting for database server to initialize...
```

#### Step 2: Check if Database Exists
```
Checking database status...
Checking if database 'technical_test' exists...
```

**Result on Fresh Install:**
- Database volume is new and empty
- MariaDB creates default system databases only
- `technical_test` database does NOT exist yet

**Important Discovery:**
Even though the volume is fresh, MariaDB's `docker-entrypoint.sh` automatically creates the database specified in `MYSQL_DATABASE` environment variable!

So the check finds:
```
Database 'technical_test' already exists.
```

#### Step 3: Check for Migrations Table
Since database exists but is empty (no tables):
```
Migrations table not found. Will run migrations...
```

#### Step 4: Run First-Time Setup
```
Running first-time setup...
Running database migrations...

   INFO  Preparing database.

  Creating migration table ..................................... 177.59ms DONE

   INFO  Running migrations.

  0001_01_01_000000_create_users_table ......................... 382.56ms DONE
  0001_01_01_000001_create_cache_table ......................... 111.00ms DONE
  0001_01_01_000002_create_jobs_table .......................... 772.52ms DONE
  2026_01_01_052747_create_products_table ....................... 92.87ms DONE
  2026_01_01_053511_create_personal_access_tokens_table ........ 184.47ms DONE
  2026_01_01_133542_create_categories_table .................... 199.05ms DONE
  2026_01_01_135316_add_category_id_to_products_table .......... 204.04ms DONE
  2026_01_01_150315_remove_category_column_from_products_table .. 26.77ms DONE
```

**All 8 migrations completed successfully!**

#### Step 5: Run Database Seeders
```
Running database seeders...

   INFO  Seeding database.

  Database\Seeders\UserSeeder ........................................ RUNNING
  Database\Seeders\UserSeeder .................................. 1,271 ms DONE

  Database\Seeders\ProductSeeder ..................................... RUNNING
  Database\Seeders\ProductSeeder ................................. 425 ms DONE
```

**Seeding completed successfully!**

The ProductSeeder now:
1. Creates 4 categories (Electronics, Furniture, Appliances, Sports)
2. Creates 20 products with proper `category_id` foreign keys

#### Step 6: Create Storage Link
```
Creating storage link...

   ERROR  The [public/storage] link already exists.
```

This "error" is harmless - the symlink exists from the Docker build context.

#### Step 7: Optimize Application
```
First-time setup completed successfully!
Optimizing application...

   INFO  Configuration cache cleared successfully.
   INFO  Application cache cleared successfully.
   INFO  Route cache cleared successfully.

Laravel setup completed successfully!
```

#### Step 8: Start PHP-FPM
```
Starting PHP-FPM...
[02-Jan-2026 10:35:57] NOTICE: fpm is running, pid 1
[02-Jan-2026 10:35:57] NOTICE: ready to handle connections
```

### 3. Database Verification

After setup completed:

```bash
docker-compose exec app php artisan tinker --execute="
  echo 'Users: ' . App\Models\User::count() . PHP_EOL;
  echo 'Categories: ' . App\Models\Category::count() . PHP_EOL;
  echo 'Products: ' . App\Models\Product::count() . PHP_EOL;
"
```

**Results:**
```
Users: 2
Categories: 4
Products: 20
```

✅ All data seeded correctly!

### 4. API Testing

**Login Test:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

**Response:**
```json
{
  "message":"Login successful",
  "user":{"id":1,"name":"Test User","email":"test@example.com"},
  "token":"1|goYWZBqwowDzA7gVONQtz2XLtV6mG7lnTUshVY1V193a2444"
}
```

✅ Authentication working!

## Why Database Migrations ARE Working

### Key Factors:

1. **MariaDB Auto-Creates Database**
   - Docker Compose sets `MYSQL_DATABASE=technical_test`
   - MariaDB's entrypoint automatically creates this database on first start
   - Our entrypoint script finds it already exists

2. **Smart Migration Detection**
   - Script checks for `migrations` table using `php artisan tinker`
   - On fresh install: table doesn't exist → runs migrations
   - On restart: table exists → skips migrations (preserves data)

3. **Fixed ProductSeeder**
   - Now creates categories BEFORE products
   - Uses `category_id` (foreign key) instead of `category` (string)
   - All 20 products seed successfully

4. **SSL Error Fixed**
   - Added `--skip-ssl` flag to mysql commands
   - No more "SSL is required" errors in logs

5. **Entrypoint Script Works**
   - Properly copied into Docker image
   - Made executable with chmod +x
   - Set as ENTRYPOINT in Dockerfile

## Total Setup Time

From `docker-compose up -d` to "ready to handle connections":
- **~70 seconds** (includes building images, creating containers, running migrations, seeding data)

## Conclusion

✅ **Database migrations ARE working correctly on fresh install!**

The automated setup process:
1. Creates database automatically
2. Runs all 8 migrations successfully
3. Seeds 2 users, 4 categories, and 20 products
4. Creates storage link
5. Optimizes Laravel
6. Starts PHP-FPM
7. Application ready to use!

**No manual intervention required!**

## User Confusion - Possible Reasons

If user reports "migrations not working", possible causes:

1. **Cached Docker images** - Old seeder code in image
   - Solution: `docker-compose up -d --build`

2. **Containers still running** - Down didn't actually stop them
   - Solution: `docker rm -f laravel-app laravel-db laravel-nginx`

3. **Old volumes still attached** - Database preserved from before
   - Solution: `docker volume rm technical_test_dbdata`

4. **Looking too early** - Migrations take 60-70 seconds
   - Solution: `docker logs laravel-app` and wait for "ready to handle connections"

5. **Wrong container logs** - Looking at nginx instead of app
   - Solution: Make sure to check `docker logs laravel-app` not `docker logs laravel-nginx`

## Recommended Fresh Install Test

To verify migrations work:

```bash
# Clean everything
docker-compose down -v
docker volume ls | grep technical_test | awk '{print $2}' | xargs docker volume rm

# Start fresh
docker-compose up -d --build

# Wait for setup (70 seconds)
sleep 70

# Check logs
docker logs laravel-app | grep -E "(migrations|seeding|DONE|FPM)"

# Verify database
docker-compose exec app php artisan tinker --execute="echo App\Models\Product::count();"
# Should output: 20

# Test API
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
# Should return token
```

If all these work, migrations ARE working! ✅

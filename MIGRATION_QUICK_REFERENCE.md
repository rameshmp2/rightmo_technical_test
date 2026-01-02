# Database Migration - Quick Reference Card

## 🚀 Most Common Commands

### Fresh Start (Complete Reset)
```bash
# Stop containers and delete all data
docker-compose down -v

# Start fresh with automatic setup
docker-compose up -d --build

# Wait for setup (important!)
sleep 70

# Verify
docker-compose exec app php artisan tinker --execute="echo App\Models\Product::count();"
# Expected: 20
```

---

## 🔧 Manual Migration Commands

### Run Fresh Migrations (⚠️ Deletes ALL data!)
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### Run Only Pending Migrations
```bash
docker-compose exec app php artisan migrate --seed
```

### Run Seeders Only
```bash
docker-compose exec app php artisan db:seed
```

### Check Migration Status
```bash
docker-compose exec app php artisan migrate:status
```

---

## 🔍 Verification Commands

### Check Database Counts
```bash
docker-compose exec app php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
echo 'Categories: ' . App\Models\Category::count() . PHP_EOL;
echo 'Products: ' . App\Models\Product::count() . PHP_EOL;
"
```

**Expected Output:**
```
Users: 2
Categories: 4
Products: 20
```

### Check Container Logs
```bash
# View all logs
docker logs laravel-app

# View last 50 lines
docker logs laravel-app | tail -50

# Search for migrations
docker logs laravel-app | grep -E "migrations|seeding"
```

### Test API
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

---

## 🐛 Troubleshooting

### Migrations not running?
```bash
# 1. Check if container is running
docker-compose ps

# 2. View logs
docker logs laravel-app

# 3. Wait longer (setup takes ~70 seconds)
sleep 70

# 4. Check logs again
docker logs laravel-app | tail -30

# 5. If still not working, force run manually
docker-compose exec app php artisan migrate:fresh --seed
```

### Database connection error?
```bash
# Check if database container is running
docker-compose ps db

# Restart database
docker-compose restart db

# Restart app
docker-compose restart app
```

### Need to rebuild?
```bash
# Rebuild just the app (preserves database)
docker-compose up -d --build app

# Full rebuild (preserves database)
docker-compose up -d --build
```

### Complete fresh start?
```bash
# Nuclear option - delete everything
docker-compose down -v
docker volume ls | grep technical_test | awk '{print $2}' | xargs docker volume rm
docker-compose up -d --build
```

---

## 📊 Database Access

### MySQL CLI Access
```bash
# Method 1: Through app container
docker-compose exec app mysql -h db -u root -proot technical_test

# Method 2: Direct to database
docker-compose exec db mysql -uroot -proot technical_test
```

### Useful SQL Queries
```sql
-- Show all tables
SHOW TABLES;

-- Check migrations ran
SELECT * FROM migrations;

-- Count products
SELECT COUNT(*) FROM products;

-- Check categories
SELECT * FROM categories;

-- Check products with categories
SELECT p.name, c.name as category
FROM products p
JOIN categories c ON p.category_id = c.id
LIMIT 5;
```

---

## ⏱️ Expected Timing

- **Container startup**: 5-10 seconds
- **Database ready**: 10-15 seconds
- **Migrations complete**: 40-50 seconds
- **Seeding complete**: 60-70 seconds
- **Total ready time**: ~70 seconds

**Don't check too early!** Wait at least 70 seconds after `docker-compose up -d` before verifying.

---

## ✅ Success Indicators

When migrations work correctly, you should see in logs:

```
Starting Laravel application setup...
Waiting for database server to initialize...
Checking database status...
Checking if database 'technical_test' exists...
Database 'technical_test' already exists.
Migrations table not found. Will run migrations...
Running first-time setup...
Running database migrations...

   INFO  Preparing database.

  Creating migration table ..................................... DONE

   INFO  Running migrations.

  0001_01_01_000000_create_users_table ......................... DONE
  0001_01_01_000001_create_cache_table ......................... DONE
  0001_01_01_000002_create_jobs_table .......................... DONE
  2026_01_01_052747_create_products_table ....................... DONE
  2026_01_01_053511_create_personal_access_tokens_table ........ DONE
  2026_01_01_133542_create_categories_table .................... DONE
  2026_01_01_135316_add_category_id_to_products_table .......... DONE
  2026_01_01_150315_remove_category_column_from_products_table . DONE

Running database seeders...

   INFO  Seeding database.

  Database\Seeders\UserSeeder .................................. DONE
  Database\Seeders\ProductSeeder ................................ DONE

Laravel setup completed successfully!
Starting PHP-FPM...
[timestamp] NOTICE: fpm is running, pid 1
[timestamp] NOTICE: ready to handle connections
```

---

## 📚 Full Documentation

For complete details, see:
- **[MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)** - Complete migration documentation
- **[FRESH_INSTALL_ANALYSIS.md](FRESH_INSTALL_ANALYSIS.md)** - Fresh install test results
- **[DOCKER_AUTOMATION.md](DOCKER_AUTOMATION.md)** - How automation works
- **[README.md](README.md)** - Main project documentation

---

## 🆘 Still Not Working?

If migrations still aren't working after trying everything above:

1. **Verify you're using the latest code:**
   ```bash
   git pull  # If using git
   docker-compose down -v
   docker-compose up -d --build
   ```

2. **Check Docker resources:**
   - Make sure Docker has enough RAM (4GB+)
   - Check disk space
   - Restart Docker Desktop

3. **Manual setup as fallback:**
   ```bash
   docker-compose exec app php artisan migrate:fresh --seed --force
   ```

4. **Check specific issues:**
   - Look for error messages in logs
   - Test database connection
   - Verify all containers are running

**Most common issue:** Not waiting long enough! Setup takes ~70 seconds.

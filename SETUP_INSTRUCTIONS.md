# Complete Setup Instructions for New Users

## ⚡ Quick Start (One Command!)

For someone receiving this project for the first time:

```bash
# Navigate to project directory
cd technical_test

# Start everything (this is the ONLY command needed!)
docker-compose up -d

# Wait for automatic setup (60-70 seconds)
# Everything happens automatically:
# ✓ Database created
# ✓ All tables created via migrations
# ✓ Sample data seeded
# ✓ Application ready!
```

**That's it!** No other commands needed.

---

## 📋 What Happens Automatically

When you run `docker-compose up -d`, the system automatically:

### 1. **Creates Database** (if doesn't exist)
- Database name: `technical_test`
- Character set: UTF8MB4
- Collation: utf8mb4_unicode_ci

### 2. **Runs ALL Migrations** (always!)
- Creates `users` table
- Creates `cache` table
- Creates `jobs` table
- Creates `products` table
- Creates `personal_access_tokens` table
- Creates `categories` table
- Adds `category_id` to products
- Removes old `category` column

### 3. **Seeds Data** (first time only)
- 2 test users
- 4 product categories
- 20 sample products

### 4. **Configures Application**
- Creates storage link
- Clears all caches
- Optimizes autoloader

---

## 🔄 How New Migrations Work

### Scenario 1: Fresh Install (First Time)
```bash
docker-compose up -d
# ✓ Creates database
# ✓ Runs ALL 8 migrations
# ✓ Seeds data (2 users, 4 categories, 20 products)
```

### Scenario 2: Adding New Migrations Later
```bash
# Developer adds new migration file
# User simply restarts containers:
docker-compose restart app

# ✓ Runs ONLY the new migration
# ✓ Skips seeders (data already exists)
# ✓ Preserves existing data
```

### Scenario 3: Rebuild After Code Changes
```bash
docker-compose up -d --build

# ✓ Rebuilds Docker images
# ✓ Runs any pending migrations
# ✓ Keeps all existing data
```

---

## ⏱️ Expected Timing

| Action | Time |
|--------|------|
| Docker build | 30-40 seconds |
| Database startup | 10-15 seconds |
| Migrations | 15-20 seconds |
| Seeding | 10-15 seconds |
| **Total ready time** | **60-70 seconds** |

**Important:** Don't check too early! Wait at least 70 seconds before testing.

---

## ✅ Verification Steps

### 1. Check if containers are running
```bash
docker-compose ps
```

**Expected output:**
```
NAME                 STATUS
laravel-app          Up (healthy)
laravel-db           Up (healthy)
laravel-nginx        Up
nextjs-frontend      Up
laravel-phpmyadmin   Up
```

### 2. Check migration logs
```bash
docker logs laravel-app | grep -E "migrations|DONE"
```

**Expected output:**
```
Running database migrations...
Creating migration table ..................................... DONE
0001_01_01_000000_create_users_table ......................... DONE
0001_01_01_000001_create_cache_table ......................... DONE
... (all 8 migrations)
```

### 3. Verify database data
```bash
docker-compose exec app php artisan tinker --execute="
echo 'Users: ' . App\Models\User::count();
echo 'Categories: ' . App\Models\Category::count();
echo 'Products: ' . App\Models\Product::count();
"
```

**Expected output:**
```
Users: 2
Categories: 4
Products: 20
```

### 4. Test the application
```bash
# Test login endpoint
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

**Expected:** Should return a JSON response with a token.

### 5. Access in browser
- **Frontend:** http://localhost:3000
- **Backend API:** http://localhost:8000
- **PhpMyAdmin:** http://localhost:8080 (user: root, pass: root)

---

## 🔧 Manual Migration Commands (Optional)

If you need to run migrations manually:

### Run pending migrations
```bash
docker-compose exec app php artisan migrate
```

### Fresh migrations (⚠️ deletes all data!)
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### Check migration status
```bash
docker-compose exec app php artisan migrate:status
```

### Rollback last migration
```bash
docker-compose exec app php artisan migrate:rollback
```

---

## 🐛 Troubleshooting

### Issue: "Containers not starting"

**Check logs:**
```bash
docker logs laravel-app
docker logs laravel-db
```

**Solution:**
```bash
docker-compose down
docker-compose up -d
```

### Issue: "Migrations not running"

**Check if database exists:**
```bash
docker-compose exec db mysql -uroot -proot -e "SHOW DATABASES;"
```

**Force run migrations:**
```bash
docker-compose exec app php artisan migrate --force
```

### Issue: "Database connection failed"

**Check database is running:**
```bash
docker-compose ps db
```

**Restart database:**
```bash
docker-compose restart db
sleep 10
docker-compose restart app
```

### Issue: "No data in database"

**Run seeders manually:**
```bash
docker-compose exec app php artisan db:seed --force
```

---

## 🔄 Common Scenarios

### Scenario: Stop and restart containers

```bash
# Stop
docker-compose down

# Start (uses existing data)
docker-compose up -d
```

**Result:**
- ✓ Uses existing database
- ✓ Runs pending migrations
- ✓ Skips seeders (data exists)

### Scenario: Complete fresh start

```bash
# Remove everything including data
docker-compose down -v

# Start fresh
docker-compose up -d
```

**Result:**
- ✓ Creates new database
- ✓ Runs all migrations
- ✓ Seeds data

### Scenario: Update code and rebuild

```bash
# Pull latest code
git pull  # if using git

# Rebuild and restart
docker-compose up -d --build
```

**Result:**
- ✓ Rebuilds Docker images
- ✓ Runs new migrations (if any)
- ✓ Preserves existing data

### Scenario: Add new migration file

```bash
# Developer adds: database/migrations/2026_01_02_XXXXX_new_table.php

# User just restarts:
docker-compose restart app

# Or rebuild:
docker-compose up -d --build
```

**Result:**
- ✓ Runs the new migration automatically
- ✓ Keeps all existing data

---

## 📊 Database Structure

After automatic setup, you'll have:

### Tables Created
1. `migrations` - Tracks which migrations have run
2. `users` - User accounts (2 seeded)
3. `cache` - Application cache
4. `jobs` - Background jobs queue
5. `products` - Products catalog (20 seeded)
6. `personal_access_tokens` - API authentication tokens
7. `categories` - Product categories (4 seeded)
8. `cache_locks` - Cache locking mechanism
9. `job_batches` - Job batch tracking
10. `failed_jobs` - Failed background jobs
11. `password_reset_tokens` - Password reset functionality
12. `sessions` - User sessions

### Sample Data

**Users:**
| Email | Password |
|-------|----------|
| test@example.com | password123 |
| admin@example.com | admin123 |

**Categories:**
- Electronics
- Furniture
- Appliances
- Sports

**Products:** 20 products across all categories

---

## 🚀 Production Deployment

For production, modify the entrypoint script to skip seeding:

**Edit `backend/docker-entrypoint.sh`:**
```bash
# Change this:
if [ "$FIRST_TIME_SETUP" = true ]; then
    php artisan db:seed --force

# To this:
if [ "$FIRST_TIME_SETUP" = true ] && [ "$APP_ENV" != "production" ]; then
    php artisan db:seed --force
```

Then in production `.env`:
```env
APP_ENV=production
```

This ensures test data is never seeded in production.

---

## 📞 Support

### View Documentation
- [README.md](README.md) - Main documentation
- [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) - Detailed migration commands
- [DOCKERFILE_FIX.md](DOCKERFILE_FIX.md) - Technical details about composer fix

### Check Logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs app
docker-compose logs db
docker-compose logs nginx
```

### Access Database
```bash
# Using PhpMyAdmin
http://localhost:8080

# Using MySQL CLI
docker-compose exec db mysql -uroot -proot technical_test
```

---

## ✨ Summary

**For new users, this is ALL you need:**

```bash
cd technical_test
docker-compose up -d
# Wait 70 seconds
# Done! App is ready at http://localhost:3000
```

Everything else happens automatically! 🎉

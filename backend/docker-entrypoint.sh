#!/bin/bash

echo "Starting Laravel application setup..."

# Wait for database server to be ready
echo "Waiting for database server to initialize..."
sleep 10

echo "Checking database status..."

# Check if database exists, create if it doesn't
DB_NAME="${DB_DATABASE:-technical_test}"
DB_HOST="${DB_HOST:-db}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-root}"

echo "Checking if database '${DB_NAME}' exists..."
DB_EXISTS=$(mysql --skip-ssl -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='${DB_NAME}'" 2>/dev/null | grep -c "${DB_NAME}")

if [ "$DB_EXISTS" -eq "0" ]; then
    echo "Database '${DB_NAME}' not found. Creating database..."
    mysql --skip-ssl -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
    echo "Database '${DB_NAME}' created successfully!"
fi

# Check if migrations table exists to determine if we need to seed
TABLES_EXIST=$(php artisan tinker --execute="
try {
    echo \DB::table('migrations')->count();
} catch (\Exception \$e) {
    echo '0';
}
" 2>/dev/null | tail -1)

if [ "$TABLES_EXIST" = "0" ] || [ -z "$TABLES_EXIST" ]; then
    echo "First-time setup detected. Will run migrations and seeders..."
    FIRST_TIME_SETUP=true
else
    echo "Database exists. Checking for pending migrations..."
    FIRST_TIME_SETUP=false
fi

# ALWAYS run migrations (new ones will run, existing ones will be skipped by Laravel)
echo "Running database migrations..."
php artisan migrate --force 2>&1

# Only seed on first-time setup
if [ "$FIRST_TIME_SETUP" = true ]; then
    echo "Running database seeders (first-time setup only)..."
    php artisan db:seed --force 2>&1
    echo "First-time setup completed successfully!"
else
    echo "Skipping seeders (database already has data)..."
fi

# Ensure storage link exists
echo "Ensuring storage link exists..."
php artisan storage:link 2>&1 || echo "Storage link already exists"

# Always clear cache on startup for fresh configuration
echo "Optimizing application..."
php artisan config:clear 2>&1
php artisan cache:clear 2>&1
php artisan route:clear 2>&1

echo "Laravel setup completed successfully!"

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm

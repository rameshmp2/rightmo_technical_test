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
    RUN_MIGRATIONS=true
else
    echo "Database '${DB_NAME}' already exists."

    # Check if migrations table exists
    TABLES_EXIST=$(php artisan tinker --execute="
    try {
        echo \DB::table('migrations')->count();
    } catch (\Exception \$e) {
        echo '0';
    }
    " 2>/dev/null | tail -1)

    if [ "$TABLES_EXIST" = "0" ] || [ -z "$TABLES_EXIST" ]; then
        echo "Migrations table not found. Will run migrations..."
        RUN_MIGRATIONS=true
    else
        echo "Database already initialized (found migrations table). Skipping migrations and seeders..."
        RUN_MIGRATIONS=false
    fi
fi

if [ "$RUN_MIGRATIONS" = true ]; then
    echo "Running first-time setup..."

    # Run migrations
    echo "Running database migrations..."
    php artisan migrate --force 2>&1

    # Run seeders
    echo "Running database seeders..."
    php artisan db:seed --force 2>&1

    # Create storage link
    echo "Creating storage link..."
    php artisan storage:link 2>&1 || echo "Storage link already exists"

    echo "First-time setup completed successfully!"
else
    # Still ensure storage link exists
    echo "Ensuring storage link exists..."
    php artisan storage:link 2>&1 || echo "Storage link already exists"
fi

# Always clear cache on startup for fresh configuration
echo "Optimizing application..."
php artisan config:clear 2>&1
php artisan cache:clear 2>&1
php artisan route:clear 2>&1

echo "Laravel setup completed successfully!"

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm

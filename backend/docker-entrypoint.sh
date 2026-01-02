#!/bin/bash

echo "Starting Laravel application setup..."

# Wait for database to be ready - simple sleep approach
echo "Waiting for database to initialize..."
sleep 10

echo "Checking database status..."

# Check if migrations table exists (indicator that migrations have been run)
TABLES_EXIST=$(php artisan tinker --execute="
try {
    echo \DB::table('migrations')->count();
} catch (\Exception \$e) {
    echo '0';
}
" 2>/dev/null | tail -1)

if [ "$TABLES_EXIST" = "0" ] || [ -z "$TABLES_EXIST" ]; then
    echo "Database tables not found. Running first-time setup..."

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
    echo "Database already initialized (found migrations table). Skipping migrations and seeders..."

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

#!/bin/bash

echo "Starting Laravel application setup..."

# Wait for database to be ready - simple sleep approach
echo "Waiting for database to initialize..."
sleep 10

echo "Attempting database connection..."

# Run migrations (without seeding first)
echo "Running database migrations..."
php artisan migrate --force 2>&1

# Only seed if tables are empty
echo "Checking if database needs seeding..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)

if [ "$USER_COUNT" -eq "0" ] 2>/dev/null; then
    echo "Database is empty, running seeders..."
    php artisan db:seed --force 2>&1
else
    echo "Database already has data (${USER_COUNT} users found), skipping seeders..."
fi

# Create storage link
echo "Creating storage link..."
php artisan storage:link 2>&1 || echo "Storage link already exists (continuing anyway)"

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear 2>&1
php artisan cache:clear 2>&1
php artisan route:clear 2>&1

echo "Laravel setup completed successfully!"

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm

#!/bin/bash

# Exit on error
set -e

echo "Starting Laravel application setup..."

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
    echo "Database not ready, waiting 2 seconds..."
    sleep 2
done

echo "Database is ready!"

# Run migrations and seeders
echo "Running database migrations and seeders..."
php artisan migrate --seed --force

# Create storage link
echo "Creating storage link..."
php artisan storage:link || true

# Clear and cache configuration
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "Laravel setup completed successfully!"

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm

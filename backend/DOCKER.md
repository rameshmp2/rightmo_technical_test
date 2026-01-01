# Backend Docker Setup Guide

This guide covers the Laravel backend Docker configuration.

For **full-stack Docker setup** (backend + frontend), see the main [docker-compose.yml](../docker-compose.yml) in the project root.

## What's Included

- **PHP 8.2-FPM**: Application server
- **Nginx**: Web server
- **MariaDB 10.4**: Database server
- **PhpMyAdmin**: Database management interface

## Docker Configurations Available

### 1. Backend Only (`backend/docker-compose.yml`)
- Runs only Laravel API and database
- Useful for backend development
- Frontend runs separately

### 2. Full-Stack (`/docker-compose.yml` in root)
- Runs both backend AND frontend containers
- Complete development environment
- **Recommended for most use cases**

## Prerequisites

- Docker installed (Docker Desktop for Windows/Mac)
- Docker Compose installed (comes with Docker Desktop)

## Quick Start

### 1. Start Docker Containers

```bash
docker-compose up -d
```

This will:
- Build the Laravel application container
- Start Nginx web server on port 8000
- Start MariaDB database on port 3307
- Start PhpMyAdmin on port 8080

### 2. Install Dependencies

```bash
docker-compose exec app composer install
```

### 3. Set Up Application

```bash
# Copy environment file (if not already done)
docker-compose exec app cp .env.example .env

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations and seeders
docker-compose exec app php artisan migrate --seed

# Create storage link
docker-compose exec app php artisan storage:link

# Set permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## Access Points

- **Application**: http://localhost:8000
- **PhpMyAdmin**: http://localhost:8080
  - Server: db
  - Username: root
  - Password: root (or as configured in .env)

## Useful Commands

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Application Commands

```bash
# Access application container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan [command]

# Run composer commands
docker-compose exec app composer [command]

# Run tests
docker-compose exec app php artisan test

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Database Commands

```bash
# Access MySQL shell
docker-compose exec db mysql -u root -p

# Create database backup
docker-compose exec db mysqldump -u root -p technical_test > backup.sql

# Restore database
docker-compose exec -T db mysql -u root -p technical_test < backup.sql

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

## Environment Configuration

Update your `.env` file for Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=technical_test
DB_USERNAME=root
DB_PASSWORD=root
```

## File Structure

```
backend/
├── Dockerfile              # PHP-FPM container configuration
├── docker-compose.yml      # Multi-container orchestration
├── .dockerignore          # Files to exclude from Docker build
└── docker/
    ├── nginx/
    │   └── conf.d/
    │       └── app.conf   # Nginx server configuration
    ├── php/
    │   └── local.ini      # PHP configuration
    └── mysql/
        └── my.cnf         # MySQL configuration
```

## Troubleshooting

### Port Already in Use

If ports 8000, 3307, or 8080 are already in use:

1. Edit `docker-compose.yml`
2. Change the port mapping:
   ```yaml
   ports:
     - "8001:80"  # Change 8000 to 8001
   ```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Failed

```bash
# Ensure database container is running
docker-compose ps

# Check database logs
docker-compose logs db

# Verify database credentials in .env match docker-compose.yml
```

### Clear All Data and Restart

```bash
# Stop and remove containers, networks, and volumes
docker-compose down -v

# Rebuild and start fresh
docker-compose up -d --build

# Reinstall dependencies and migrate
docker-compose exec app composer install
docker-compose exec app php artisan migrate:fresh --seed
```

## Production Deployment

For production, consider:

1. **Use separate .env file**: Create `.env.production`
2. **Secure secrets**: Don't commit passwords to git
3. **Enable HTTPS**: Add SSL certificates to Nginx
4. **Optimize images**: Use multi-stage builds
5. **Resource limits**: Set memory and CPU limits
6. **Health checks**: Add container health checks
7. **Logging**: Configure centralized logging
8. **Monitoring**: Add monitoring tools (Prometheus, etc.)

## Performance Optimization

### PHP-FPM Tuning

Edit `docker/php/local.ini`:

```ini
pm.max_children = 50
pm.start_servers = 20
pm.min_spare_servers = 10
pm.max_spare_servers = 30
```

### Nginx Caching

Edit `docker/nginx/conf.d/app.conf` to add caching headers.

### Database Optimization

Edit `docker/mysql/my.cnf` for performance tuning.

## Scaling

To run multiple application instances:

```bash
docker-compose up -d --scale app=3
```

Add a load balancer (Nginx/HAProxy) in front of the app containers.

## Backup Strategy

### Automated Backups

Create a backup script:

```bash
#!/bin/bash
docker-compose exec -T db mysqldump -u root -proot technical_test > "backup-$(date +%Y%m%d-%H%M%S).sql"
```

Schedule with cron:

```bash
0 2 * * * /path/to/backup.sh
```

## Monitoring

View container resource usage:

```bash
docker stats
```

## Network

All containers are on the same network (`laravel-network`) and can communicate using service names:
- app
- nginx
- db
- phpmyadmin

## Volumes

Persistent data is stored in Docker volumes:
- `dbdata`: Database files

To back up volumes:

```bash
docker run --rm -v backend_dbdata:/data -v $(pwd):/backup alpine tar czf /backup/dbdata-backup.tar.gz /data
```

---

This Docker setup provides a complete, isolated development environment that matches production configuration.

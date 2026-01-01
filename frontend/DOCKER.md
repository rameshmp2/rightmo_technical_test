# Frontend Docker Setup Guide

This guide explains how to run the Next.js frontend using Docker.

## Prerequisites

- Docker installed (20.10 or higher)
- Docker Compose installed (1.29 or higher)

## Docker Configuration

### Dockerfile

The frontend uses a Node.js 25.2.1 Alpine image for a lightweight container.

**Key Features**:
- Based on `node:25.2.1-alpine`
- Installs all npm dependencies
- Exposes port 3000
- Runs Next.js development server by default

### docker-compose.yml

Two Docker Compose configurations are available:

1. **Frontend Only** (`frontend/docker-compose.yml`)
   - Runs only the Next.js application
   - Useful for frontend development

2. **Full-Stack** (`docker-compose.yml` in root)
   - Runs both backend and frontend together
   - Complete development environment

## Quick Start

### Option 1: Frontend Only

Run just the Next.js application:

```bash
cd frontend
docker-compose up -d
```

Access the application at: **http://localhost:3000**

### Option 2: Full-Stack Setup (Recommended)

Run the entire application (backend + frontend + database):

```bash
# From project root
docker-compose up -d

# Install backend dependencies
docker-compose exec app composer install

# Run migrations
docker-compose exec app php artisan migrate --seed
```

**Services Available**:
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000
- PhpMyAdmin: http://localhost:8080
- Database: localhost:3307

## Docker Commands

### Start Services

```bash
# Start all services
docker-compose up -d

# Start in foreground (see logs)
docker-compose up

# Start specific service
docker-compose up -d frontend
```

### Stop Services

```bash
# Stop all services
docker-compose down

# Stop and remove volumes
docker-compose down -v

# Stop specific service
docker-compose stop frontend
```

### View Logs

```bash
# View all logs
docker-compose logs

# View frontend logs
docker-compose logs frontend

# Follow logs in real-time
docker-compose logs -f frontend
```

### Execute Commands

```bash
# Access frontend container shell
docker-compose exec frontend sh

# Run npm commands
docker-compose exec frontend npm install
docker-compose exec frontend npm run build

# Clear Next.js cache
docker-compose exec frontend rm -rf .next
```

## Development Workflow

### Making Code Changes

The frontend container uses volume mounting, so code changes are reflected immediately:

1. Edit files in `frontend/` directory
2. Next.js hot-reload will detect changes
3. Browser automatically refreshes

### Installing New Packages

```bash
# Install a new package
docker-compose exec frontend npm install <package-name>

# Install dev dependency
docker-compose exec frontend npm install -D <package-name>

# Update package
docker-compose exec frontend npm update <package-name>
```

### Rebuilding the Container

If you modify `Dockerfile` or need a fresh build:

```bash
# Rebuild frontend container
docker-compose build frontend

# Rebuild and restart
docker-compose up -d --build frontend
```

## Environment Variables

The frontend container uses these environment variables:

```yaml
environment:
  - NODE_ENV=development
  - NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

**To modify**:
1. Edit `docker-compose.yml` environment section
2. Restart the container: `docker-compose restart frontend`

## Volume Mounts

The configuration uses the following volume mounts:

```yaml
volumes:
  - ./frontend:/app              # Source code
  - /app/node_modules            # Persist dependencies
  - /app/.next                   # Persist build cache
```

**Benefits**:
- Live code reloading
- Persistent node_modules (faster rebuilds)
- Next.js build cache preserved

## Port Mapping

**Container Port**: 3000
**Host Port**: 3000

To change the host port, edit `docker-compose.yml`:

```yaml
ports:
  - "3001:3000"  # Access at http://localhost:3001
```

## Networking

### Frontend Only Setup

Uses `frontend-network` bridge network for container isolation.

### Full-Stack Setup

Uses `fullstack-network` shared between all services:
- Frontend can communicate with backend
- All services isolated from host network
- Accessible via service names (e.g., `http://app:9000`)

## Production Build

To create a production build:

### Option 1: Using Docker

```bash
# Create production Dockerfile
cat > Dockerfile.prod << 'EOF'
FROM node:25.2.1-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production
COPY . .
RUN npm run build

FROM node:25.2.1-alpine
WORKDIR /app
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package*.json ./
COPY --from=builder /app/public ./public

EXPOSE 3000
ENV NODE_ENV=production
CMD ["npm", "start"]
EOF

# Build production image
docker build -f Dockerfile.prod -t nextjs-frontend:prod .

# Run production container
docker run -d -p 3000:3000 \
  -e NEXT_PUBLIC_API_URL=https://api.yourapp.com/api \
  nextjs-frontend:prod
```

### Option 2: Traditional Build

```bash
# Build inside container
docker-compose exec frontend npm run build

# Start production server
docker-compose exec frontend npm start
```

## Troubleshooting

### Port Already in Use

```bash
# Check what's using port 3000
# Windows
netstat -ano | findstr :3000

# Stop the conflicting process or change port in docker-compose.yml
```

### Container Won't Start

```bash
# View detailed logs
docker-compose logs frontend

# Common issues:
# 1. Port conflict - change port mapping
# 2. npm install failed - rebuild container
# 3. Syntax error in code - check logs
```

### Dependencies Not Installing

```bash
# Remove node_modules and reinstall
docker-compose exec frontend rm -rf node_modules
docker-compose exec frontend npm install

# Or rebuild container
docker-compose down
docker-compose up -d --build frontend
```

### Hot Reload Not Working

```bash
# Restart the container
docker-compose restart frontend

# Or rebuild
docker-compose up -d --build frontend
```

### Cannot Connect to Backend

**Check**:
1. Backend container is running: `docker-compose ps`
2. Backend URL is correct in `.env.local`
3. Both containers are on same network (full-stack setup)

```bash
# Test backend from frontend container
docker-compose exec frontend wget -O- http://localhost:8000/api/categories
```

### Clear Everything and Start Fresh

```bash
# Stop and remove everything
docker-compose down -v

# Remove images
docker rmi nextjs-frontend

# Start fresh
docker-compose up -d --build
```

## Performance Optimization

### For Development

The current setup is optimized for development:
- Volume mounts for live reloading
- Development dependencies included
- Source maps enabled

### For Production

Use multi-stage builds:
- Smaller image size
- Only production dependencies
- Optimized Next.js build
- See "Production Build" section above

## Best Practices

1. **Use .dockerignore**: Already configured to exclude unnecessary files
2. **Volume Mounts**: Persist node_modules for faster rebuilds
3. **Environment Variables**: Never commit secrets to docker-compose.yml
4. **Networking**: Use service names for inter-container communication
5. **Logs**: Regularly check logs for errors
6. **Updates**: Keep base image updated (`node:25.2.1-alpine`)

## Integration with Backend

When using full-stack setup:

```bash
# Start all services
docker-compose up -d

# Backend setup
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed

# Access services
# Frontend: http://localhost:3000
# Backend: http://localhost:8000
# Database: localhost:3307
```

The frontend automatically connects to the backend via `NEXT_PUBLIC_API_URL`.

## Useful Commands Reference

```bash
# Status
docker-compose ps

# Logs
docker-compose logs -f frontend

# Shell access
docker-compose exec frontend sh

# Restart
docker-compose restart frontend

# Rebuild
docker-compose up -d --build frontend

# Stop
docker-compose stop frontend

# Remove
docker-compose down

# Remove with volumes
docker-compose down -v
```

## File Structure

```
frontend/
├── Dockerfile                 # Frontend container definition
├── .dockerignore             # Files to exclude from build
├── docker-compose.yml        # Frontend-only compose file
└── DOCKER.md                 # This file

Root:
└── docker-compose.yml        # Full-stack compose file
```

## Additional Resources

- [Next.js Docker Documentation](https://nextjs.org/docs/deployment#docker-image)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Node.js Docker Best Practices](https://github.com/nodejs/docker-node/blob/main/docs/BestPractices.md)

---

**Frontend Docker setup is production-ready!** 🐳

For the complete full-stack Docker setup, see the root `docker-compose.yml` file.

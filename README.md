# Product Management System

A full-stack product management application built with Laravel (Backend) and Next.js (Frontend).

## Features

### Backend (Laravel API)
- **Authentication**: Token-based authentication using Laravel Sanctum
- **Product CRUD**: Complete create, read, update, delete operations
- **Search & Filter**: Search by product name, filter by category and price range
- **Sorting**: Sort products by price, rating, or date
- **Pagination**: Paginated product listing
- **Image Upload**: Product image upload with automatic storage management
- **Validation**: Comprehensive input validation and error handling
- **Unique Constraint**: Product names are unique across the system

### Frontend (Next.js)
- **SSR Support**: Server-Side Rendering with Next.js App Router
- **Authentication**: Login/logout functionality with protected routes
- **Product Dashboard**: Responsive product listing with search, filter, sort, and pagination
- **Product Detail Pages**: Dynamic routing for individual product views
- **Image Upload UI**: User-friendly interface for uploading product images
- **Responsive Design**: Mobile-first design using Tailwind CSS
- **Type Safety**: Full TypeScript implementation

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Sanctum
- **Database**: MySQL (MariaDB 10.4.32)
- **PHP Version**: 8.2.12
- **API Structure**: RESTful API

### Frontend
- **Framework**: Next.js 15 (App Router)
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **HTTP Client**: Axios
- **Node.js**: v25.2.1

## Project Structure

```
technical_test/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   │           ├── AuthController.php
│   │   │           └── ProductController.php
│   │   └── Models/
│   │       ├── Product.php
│   │       └── User.php
│   ├── config/
│   │   ├── cors.php
│   │   └── sanctum.php
│   ├── database/
│   │   ├── migrations/
│   │   │   └── xxxx_create_products_table.php
│   │   └── seeders/
│   │       ├── ProductSeeder.php
│   │       └── UserSeeder.php
│   └── routes/
│       └── api.php
│
└── frontend/               # Next.js Application
    ├── app/
    │   ├── login/
    │   ├── dashboard/
    │   └── products/
    ├── components/
    ├── lib/
    └── public/

```

## Installation & Setup

### 🐳 Quick Start with Docker (Recommended)

**For new users receiving this project:** You only need ONE command!

```bash
cd technical_test
docker-compose up -d
```

**That's it!** Wait 70 seconds and everything is ready:
- ✅ Database automatically created
- ✅ All tables created via migrations
- ✅ Sample data seeded (2 users, 4 categories, 20 products)
- ✅ Application ready at http://localhost:3000

📖 **[Complete Setup Instructions](SETUP_INSTRUCTIONS.md)** - Detailed guide for new users

---

#### Prerequisites
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose (included with Docker Desktop)

#### Detailed Setup Steps

1. **Clone the repository and navigate to the project:**
   ```bash
   cd technical_test
   ```

2. **Start all services using Docker Compose:**
   ```bash
   docker-compose up -d
   ```

   This single command will:
   - Build and start 5 services (Frontend, Backend, Nginx, MariaDB, PhpMyAdmin)
   - Set up networking between containers
   - **Automatically create database** (`technical_test`)
   - **Automatically run migrations and seeders** (first time only)
   - **Automatically create storage link**
   - Configure all environment variables automatically

   **Smart Setup Behavior:**
   - ✅ **First run** (no database) → Creates database + runs migrations + seeds data
   - ✅ **Restart/rebuild** (existing database) → Skips everything, preserves your data
   - ✅ **Fresh start** (`docker-compose down -v`) → Creates database + full setup again

   **Zero Manual Configuration Required!**

3. **Access the application:**
   - **Frontend**: [http://localhost:3000](http://localhost:3000)
   - **Backend API**: [http://localhost:8000](http://localhost:8000)
   - **PhpMyAdmin**: [http://localhost:8080](http://localhost:8080)
     - Username: `root`
     - Password: `root`

4. **Login with test credentials:**
   - Email: `test@example.com`
   - Password: `password123`

That's it! The application is fully set up and ready to use.

#### Docker Services Overview

The Docker setup includes 5 containerized services:

| Service | Container Name | Port | Description |
|---------|---------------|------|-------------|
| Frontend | `nextjs-frontend` | 3000 | Next.js application |
| Backend | `laravel-app` | 9000 | PHP-FPM (Laravel API) |
| Nginx | `laravel-nginx` | 8000 | Web server for backend |
| Database | `laravel-db` | 3306 | MariaDB 10.4 |
| PhpMyAdmin | `laravel-phpmyadmin` | 8080 | Database management UI |


### Manual Installation (Alternative)

If you prefer to run the project without Docker, follow these manual setup steps:

#### Prerequisites
- PHP 8.2.12
- Composer
- MySQL (MariaDB 10.4.32) or compatible
- Node.js v25.2.1
- npm or yarn

#### Backend Setup

1. **Navigate to the backend directory:**
   ```bash
   cd backend
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment:**
   - Copy `.env.example` to `.env` (already done during installation)
   - The database configuration is already set for MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=technical_test
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Create the database:**
   Open MySQL/phpMyAdmin and run:
   ```sql
   CREATE DATABASE technical_test;
   ```

5. **Run migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Create storage link for image uploads:**
   ```bash
   php artisan storage:link
   ```

7. **Start the Laravel development server:**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`

### Frontend Setup

1. **Navigate to the frontend directory:**
   ```bash
   cd frontend
   ```

2. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

3. **Configure environment:**
   Create a `.env.local` file in the frontend directory:
   ```env
   NEXT_PUBLIC_API_URL=http://localhost:8000/api
   ```

4. **Start the Next.js development server:**
   ```bash
   npm run dev
   ```
   The frontend will be available at `http://localhost:3000`

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout (protected)
- `GET /api/user` - Get authenticated user (protected)

### Products
All product endpoints require authentication.

- `GET /api/products` - List all products with pagination
  - Query parameters:
    - `search` - Search by product name
    - `category` - Filter by category
    - `min_price` - Minimum price filter
    - `max_price` - Maximum price filter
    - `sort_by` - Sort field (price, rating, name, created_at)
    - `sort_order` - Sort direction (asc, desc)
    - `per_page` - Items per page (default: 10)
    - `page` - Page number

- `GET /api/products/{id}` - Get single product
- `POST /api/products` - Create new product
- `PUT/PATCH /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/categories` - Get all unique categories

## Default Test Credentials

Two test users are created during seeding:

**User 1:**
- Email: `test@example.com`
- Password: `password123`

**User 2:**
- Email: `admin@example.com`
- Password: `admin123`

## Key Design Decisions

### Authentication Approach
- **Laravel Sanctum** was chosen over JWT for its seamless integration with Laravel and better security for SPAs
- Token-based authentication allows stateless API requests
- Tokens are stored in localStorage on the frontend for persistence
- All product routes are protected and require authentication

### Image Handling
- Images are stored in Laravel's `storage/app/public/products` directory
- Symbolic link created between `storage/app/public` and `public/storage`
- Images are served through Laravel's public disk
- Validation: Maximum 2MB, accepted formats: JPEG, PNG, JPG, GIF
- Old images are automatically deleted when products are updated or removed

### Database Design
- **Products table** includes: id, name (unique), category, price, rating, image, description, timestamps
- **Unique constraint** on product name prevents duplicates
- **Decimal types** for price and rating ensure accurate numerical representation
- **Soft deletes** not implemented as per requirements (hard delete used)

### Frontend Architecture
- **Next.js App Router** for modern React with SSR support
- **TypeScript** for type safety and better developer experience
- **Tailwind CSS** for rapid, responsive UI development
- **Protected routes** using middleware and authentication context
- **Axios** for API communication with interceptors for token management

### Pagination Strategy
- Server-side pagination to handle large datasets efficiently
- Default 10 items per page, configurable via API parameter
- Laravel's built-in pagination with full metadata (current page, total pages, etc.)

## File Structure Highlights

### Backend Controllers
- `AuthController` [backend/app/Http/Controllers/Api/AuthController.php](backend/app/Http/Controllers/Api/AuthController.php) - Handles login, logout, and user authentication
- `ProductController` [backend/app/Http/Controllers/Api/ProductController.php](backend/app/Http/Controllers/Api/ProductController.php) - CRUD operations with search, filter, sort, and pagination

### Frontend Pages
- Login page with form validation
- Protected dashboard with product listing
- Product detail pages using dynamic routing (`/products/[id]`)
- Image upload component integrated in product forms

## Testing the Application

1. **Start both servers:**
   - Backend: `cd backend && php artisan serve`
   - Frontend: `cd frontend && npm run dev`

2. **Access the application:**
   - Open browser to `http://localhost:3000`
   - Login with test credentials
   - Navigate to the product dashboard

3. **Test features:**
   - Search for products by name
   - Filter by category using dropdown
   - Filter by price range
   - Sort by price or rating
   - View product details
   - Upload/update product images
   - Create, update, and delete products

## Troubleshooting

### Database Connection Issues
- Ensure MySQL is running
- Verify database credentials in `.env`
- Check if database `technical_test` exists

### Image Upload Issues
- Run `php artisan storage:link` if images don't display
- Check folder permissions on `storage/app/public`

### CORS Errors
- Verify the frontend URL is allowed in `backend/config/cors.php`
- Ensure API URL in frontend `.env.local` is correct

### Authentication Issues
- Clear browser localStorage and try logging in again
- Verify Sanctum configuration in `backend/config/sanctum.php`

### Docker: vendor/autoload.php Not Found Error
**Error:** `Fatal error: Failed opening required '/var/www/vendor/autoload.php'`

**Quick Fix:**
```bash
# Install dependencies inside container
docker exec laravel-app composer install --no-interaction --optimize-autoloader

# Verify it worked
docker exec laravel-app php artisan --version
```

**Permanent Fix:** This issue is now resolved in the docker-compose.yml with an anonymous volume for `/var/www/vendor`. See [VENDOR_DIRECTORY_FIX.md](VENDOR_DIRECTORY_FIX.md) for details.

### Docker: Migrations Not Running
**Solution:**
```bash
# Wait at least 70 seconds after starting containers
sleep 70

# Check logs to verify migrations ran
docker logs laravel-app | grep "Running database migrations"

# Manual fallback if needed
docker exec laravel-app php artisan migrate:fresh --seed --force
```

See [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) for comprehensive troubleshooting.

## Production Deployment Notes

For production deployment, consider:

1. **Backend:**
   - Set `APP_ENV=production` in `.env`
   - Set `APP_DEBUG=false`
   - Configure proper CORS origins (remove `*`)
   - Use environment-specific database credentials
   - Set up proper file storage (S3, etc.)
   - Configure HTTPS

2. **Frontend:**
   - Build production bundle: `npm run build`
   - Update `NEXT_PUBLIC_API_URL` to production API URL
   - Configure proper environment variables
   - Enable production optimizations

## Additional Documentation

This project includes comprehensive documentation beyond this README:


- **[backend/API_DOCUMENTATION.md](backend/API_DOCUMENTATION.md)** - Complete API reference

### 📊 Project Information
- **[backend/README.md](backend/README.md)** - Backend-specific documentation
- **[frontend/README.md](frontend/README.md)** - Frontend-specific documentation


2. **PHPUnit Tests** - 34 comprehensive automated tests ✅
   - 9 authentication tests
   - 25 product API tests
   - See [TESTING.md](backend/TESTING.md)

3. **API Documentation** - Professional comprehensive documentation 📚
   - All endpoints documented with examples
   - See [API_DOCUMENTATION.md](backend/API_DOCUMENTATION.md)


## License

This project is created as a technical assessment for Rightmo Web Solution.

## Developer Notes

- All code is documented with comments explaining logic
- Input validation is comprehensive on both frontend and backend
- Error handling provides meaningful messages to users
- Security best practices followed (CSRF protection, SQL injection prevention, XSS protection)
- Code follows Laravel and React/Next.js best practices

# Product Management Backend

Laravel API backend for the Product Management System.

## Features

- RESTful API with Laravel 12
- Token-based authentication using Laravel Sanctum
- Product CRUD operations with validation
- Search, filter, sort, and pagination
- Image upload handling
- Database migrations and seeders
- Comprehensive error handling

## Tech Stack

- Laravel 12.x
- PHP 8.2.12
- MySQL (MariaDB 10.4.32)
- Laravel Sanctum for authentication

## Setup

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   The `.env` file is already configured for MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=technical_test
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Create database:**
   ```sql
   CREATE DATABASE technical_test;
   ```

4. **Run migrations and seed database:**
   ```bash
   php artisan migrate --seed
   ```

5. **Create storage link:**
   ```bash
   php artisan storage:link
   ```

6. **Start development server:**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout (protected)
- `GET /api/user` - Get authenticated user (protected)

### Products (All protected)
- `GET /api/products` - List products with filters
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product
- `PUT/PATCH /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/categories` - Get all categories

### Query Parameters for Product Listing

- `search` - Search by product name
- `category` - Filter by category
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `sort_by` - Sort field (price, rating, name, created_at)
- `sort_order` - Sort direction (asc, desc)
- `per_page` - Items per page (default: 10)
- `page` - Page number

## Test Credentials

Two test users are seeded:

**User 1:**
- Email: test@example.com
- Password: password123

**User 2:**
- Email: admin@example.com
- Password: admin123

## Database Schema

### Products Table
- `id` - Primary key
- `name` - String (unique)
- `category` - String
- `price` - Decimal(10,2)
- `rating` - Decimal(3,2)
- `image` - String (nullable)
- `description` - Text (nullable)
- `created_at` - Timestamp
- `updated_at` - Timestamp

### Users Table
- Standard Laravel users table with Sanctum support

## Image Storage

Images are stored in `storage/app/public/products/` and served via the public disk after running `php artisan storage:link`.

## Validation Rules

### Product Creation
- `name`: required, unique, max 255 characters
- `category`: required, max 255 characters
- `price`: required, numeric, min 0
- `rating`: optional, numeric, 0-5
- `description`: optional, text
- `image`: optional, image (jpeg, png, jpg, gif), max 2MB

### Authentication
- `email`: required, email format
- `password`: required

## Error Handling

The API returns consistent JSON error responses with appropriate HTTP status codes:
- 200: Success
- 201: Created
- 401: Unauthorized
- 404: Not Found
- 422: Validation Error
- 500: Server Error

---

## About Laravel

This project is built with Laravel. For more information about the Laravel framework, see the [official Laravel documentation](https://laravel.com/docs).

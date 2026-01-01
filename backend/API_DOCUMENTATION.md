# Product Management API Documentation

Complete REST API documentation for the Product Management System.

**Base URL**: `http://localhost:8000/api`

**Version**: 1.0

**Authentication**: Bearer Token (Laravel Sanctum)

---

## Table of Contents

1. [Authentication](#authentication)
2. [Products](#products)
3. [Categories](#categories)
4. [Error Responses](#error-responses)
5. [Rate Limiting](#rate-limiting)

---

## Authentication

### Login

Authenticate a user and receive an access token.

**Endpoint**: `POST /login`

**Authentication**: None (Public)

**Request Body**:
```json
{
  "email": "string (required, email format)",
  "password": "string (required)"
}
```

**Success Response** (200 OK):
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  },
  "token": "1|abcdef123456..."
}
```

**Error Responses**:

422 Unprocessable Entity (Invalid Credentials):
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

422 Unprocessable Entity (Validation Error):
```json
{
  "message": "The email field is required. (and 1 more error)",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

**Example**:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

---

### Logout

Revoke the current access token.

**Endpoint**: `POST /logout`

**Authentication**: Required (Bearer Token)

**Request Headers**:
```
Authorization: Bearer {token}
```

**Success Response** (200 OK):
```json
{
  "message": "Logout successful"
}
```

**Error Response**:

401 Unauthorized:
```json
{
  "message": "Unauthenticated."
}
```

**Example**:
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

### Get Current User

Retrieve the authenticated user's details.

**Endpoint**: `GET /user`

**Authentication**: Required (Bearer Token)

**Success Response** (200 OK):
```json
{
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "email_verified_at": null,
    "created_at": "2026-01-01T10:00:00.000000Z",
    "updated_at": "2026-01-01T10:00:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|abcdef123456..."
```

---

## Products

All product endpoints require authentication.

### List Products

Retrieve a paginated list of products with optional filtering, sorting, and searching.

**Endpoint**: `GET /products`

**Authentication**: Required (Bearer Token)

**Query Parameters**:

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search products by name | `?search=Laptop` |
| `category` | string | Filter by category | `?category=Electronics` |
| `min_price` | number | Minimum price filter | `?min_price=100` |
| `max_price` | number | Maximum price filter | `?max_price=1000` |
| `sort_by` | string | Sort field (price, rating, name, created_at) | `?sort_by=price` |
| `sort_order` | string | Sort direction (asc, desc) | `?sort_order=asc` |
| `per_page` | number | Items per page (default: 10) | `?per_page=20` |
| `page` | number | Page number | `?page=2` |

**Success Response** (200 OK):
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "Laptop Pro 15",
      "category": "Electronics",
      "price": "1299.99",
      "rating": "4.50",
      "image": "products/1234567890_laptop.jpg",
      "description": "High-performance laptop with 16GB RAM",
      "created_at": "2026-01-01T10:00:00.000000Z",
      "updated_at": "2026-01-01T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Wireless Mouse",
      "category": "Electronics",
      "price": "29.99",
      "rating": "4.20",
      "image": null,
      "description": "Ergonomic wireless mouse",
      "created_at": "2026-01-01T10:00:00.000000Z",
      "updated_at": "2026-01-01T10:00:00.000000Z"
    }
  ],
  "first_page_url": "http://localhost:8000/api/products?page=1",
  "from": 1,
  "last_page": 2,
  "last_page_url": "http://localhost:8000/api/products?page=2",
  "links": [...],
  "next_page_url": "http://localhost:8000/api/products?page=2",
  "path": "http://localhost:8000/api/products",
  "per_page": 10,
  "prev_page_url": null,
  "to": 10,
  "total": 20
}
```

**Examples**:

```bash
# Basic listing
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}"

# Search by name
curl -X GET "http://localhost:8000/api/products?search=Laptop" \
  -H "Authorization: Bearer {token}"

# Filter by category and price range
curl -X GET "http://localhost:8000/api/products?category=Electronics&min_price=100&max_price=1000" \
  -H "Authorization: Bearer {token}"

# Sort by price (low to high)
curl -X GET "http://localhost:8000/api/products?sort_by=price&sort_order=asc" \
  -H "Authorization: Bearer {token}"

# Combined filters
curl -X GET "http://localhost:8000/api/products?search=Laptop&category=Electronics&sort_by=price&sort_order=asc&per_page=20" \
  -H "Authorization: Bearer {token}"
```

---

### Get Single Product

Retrieve details of a specific product.

**Endpoint**: `GET /products/{id}`

**Authentication**: Required (Bearer Token)

**URL Parameters**:
- `id` (integer, required): Product ID

**Success Response** (200 OK):
```json
{
  "product": {
    "id": 1,
    "name": "Laptop Pro 15",
    "category": "Electronics",
    "price": "1299.99",
    "rating": "4.50",
    "image": "products/1234567890_laptop.jpg",
    "description": "High-performance laptop with 16GB RAM and 512GB SSD",
    "created_at": "2026-01-01T10:00:00.000000Z",
    "updated_at": "2026-01-01T10:00:00.000000Z"
  }
}
```

**Error Response**:

404 Not Found:
```json
{
  "message": "Product not found"
}
```

**Example**:
```bash
curl -X GET http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}"
```

---

### Create Product

Create a new product.

**Endpoint**: `POST /products`

**Authentication**: Required (Bearer Token)

**Request Body** (multipart/form-data or JSON):

| Field | Type | Required | Constraints |
|-------|------|----------|-------------|
| `name` | string | Yes | Max 255 chars, must be unique |
| `category` | string | Yes | Max 255 chars |
| `price` | number | Yes | Min: 0 |
| `rating` | number | No | Min: 0, Max: 5 |
| `description` | text | No | - |
| `image` | file | No | Image (jpeg, png, jpg, gif), Max: 2MB |

**Success Response** (201 Created):
```json
{
  "message": "Product created successfully",
  "product": {
    "id": 21,
    "name": "New Product",
    "category": "Electronics",
    "price": "199.99",
    "rating": "4.50",
    "image": "products/1704096000_product.jpg",
    "description": "Product description",
    "created_at": "2026-01-01T12:00:00.000000Z",
    "updated_at": "2026-01-01T12:00:00.000000Z"
  }
}
```

**Error Responses**:

422 Unprocessable Entity (Validation Error):
```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "category": ["The category field is required."],
    "price": ["The price field is required."],
    "rating": ["The rating field must be between 0 and 5."],
    "image": ["The image field must be an image."]
  }
}
```

422 Unprocessable Entity (Duplicate Name):
```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name has already been taken."]
  }
}
```

**Examples**:

```bash
# Create product without image (JSON)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Laptop",
    "category": "Electronics",
    "price": 999.99,
    "rating": 4.5,
    "description": "A great laptop"
  }'

# Create product with image (multipart/form-data)
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}" \
  -F "name=New Laptop" \
  -F "category=Electronics" \
  -F "price=999.99" \
  -F "rating=4.5" \
  -F "description=A great laptop" \
  -F "image=@/path/to/image.jpg"
```

---

### Update Product

Update an existing product.

**Endpoint**: `PUT /products/{id}` or `PATCH /products/{id}`

**Authentication**: Required (Bearer Token)

**URL Parameters**:
- `id` (integer, required): Product ID

**Request Body** (same as Create, all fields optional):

**Success Response** (200 OK):
```json
{
  "message": "Product updated successfully",
  "product": {
    "id": 1,
    "name": "Updated Laptop Name",
    "category": "Electronics",
    "price": "1499.99",
    "rating": "4.80",
    "image": "products/1704096000_new_image.jpg",
    "description": "Updated description",
    "created_at": "2026-01-01T10:00:00.000000Z",
    "updated_at": "2026-01-01T12:30:00.000000Z"
  }
}
```

**Error Responses**:

404 Not Found:
```json
{
  "message": "Product not found"
}
```

422 Unprocessable Entity (Validation Error):
```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name has already been taken."],
    "price": ["The price field must be a number."]
  }
}
```

**Examples**:

```bash
# Update product (JSON)
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Product Name",
    "price": 1499.99
  }'

# Update product with new image (multipart/form-data)
curl -X POST http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}" \
  -F "name=Updated Product" \
  -F "price=1499.99" \
  -F "image=@/path/to/new_image.jpg"
```

---

### Delete Product

Delete a product.

**Endpoint**: `DELETE /products/{id}`

**Authentication**: Required (Bearer Token)

**URL Parameters**:
- `id` (integer, required): Product ID

**Success Response** (200 OK):
```json
{
  "message": "Product deleted successfully"
}
```

**Error Response**:

404 Not Found:
```json
{
  "message": "Product not found"
}
```

**Example**:
```bash
curl -X DELETE http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}"
```

---

## Categories

### Get All Categories

Retrieve a list of all unique product categories.

**Endpoint**: `GET /categories`

**Authentication**: Required (Bearer Token)

**Success Response** (200 OK):
```json
{
  "categories": [
    "Electronics",
    "Furniture",
    "Appliances",
    "Sports"
  ]
}
```

**Example**:
```bash
curl -X GET http://localhost:8000/api/categories \
  -H "Authorization: Bearer {token}"
```

---

## Error Responses

### Standard Error Format

All API errors follow this format:

```json
{
  "message": "Error message description",
  "errors": {
    "field_name": [
      "Specific validation error message"
    ]
  }
}
```

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request succeeded |
| 201 | Created | Resource created successfully |
| 401 | Unauthorized | Missing or invalid authentication token |
| 404 | Not Found | Requested resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

### Common Error Scenarios

**401 Unauthorized**:
```json
{
  "message": "Unauthenticated."
}
```

Causes:
- Missing Authorization header
- Invalid or expired token
- Token has been revoked

**422 Validation Error**:
```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a number."]
  }
}
```

Causes:
- Missing required fields
- Invalid data types
- Constraint violations (unique, min, max, etc.)

**404 Not Found**:
```json
{
  "message": "Product not found"
}
```

Causes:
- Invalid product ID
- Product has been deleted

---

## Rate Limiting

The API implements rate limiting to prevent abuse.

**Default Limits**:
- 60 requests per minute per IP address
- Authenticated requests: 1000 requests per minute

**Rate Limit Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1704096000
```

**429 Too Many Requests**:
```json
{
  "message": "Too Many Attempts."
}
```

---

## Pagination

All list endpoints return paginated results with metadata:

```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "http://...",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://...",
  "next_page_url": "http://...",
  "path": "http://...",
  "per_page": 10,
  "prev_page_url": null,
  "to": 10,
  "total": 50
}
```

**Pagination Parameters**:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10, max: 100)

---

## Image Handling

### Uploading Images

- **Accepted formats**: JPEG, PNG, JPG, GIF
- **Maximum size**: 2MB
- **Field name**: `image`
- **Content-Type**: `multipart/form-data`

### Accessing Images

Images are stored in the public storage and accessible at:

```
http://localhost:8000/storage/products/{filename}
```

Example:
```
http://localhost:8000/storage/products/1704096000_product.jpg
```

### Image in Responses

The `image` field in product responses contains the relative path:

```json
{
  "image": "products/1704096000_product.jpg"
}
```

To display the image, prepend the storage URL:
```javascript
const imageUrl = `http://localhost:8000/storage/${product.image}`;
```

---

## Best Practices

### Authentication

1. **Store tokens securely**: Use httpOnly cookies or secure storage
2. **Refresh tokens**: Implement token refresh logic
3. **Logout on 401**: Clear tokens and redirect to login

### Error Handling

```javascript
try {
  const response = await fetch('/api/products', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    if (response.status === 401) {
      // Handle unauthorized
      redirectToLogin();
    } else if (response.status === 422) {
      // Handle validation errors
      const errors = await response.json();
      displayValidationErrors(errors.errors);
    }
  }

  const data = await response.json();
} catch (error) {
  console.error('Network error:', error);
}
```

### Image Uploads

```javascript
const formData = new FormData();
formData.append('name', 'Product Name');
formData.append('category', 'Electronics');
formData.append('price', 99.99);
formData.append('image', fileInput.files[0]);

fetch('/api/products', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
    // Don't set Content-Type for FormData
  },
  body: formData
});
```

---

## Testing with Postman

### 1. Import Collection

Create a Postman collection with these endpoints.

### 2. Set Environment Variables

```
base_url: http://localhost:8000/api
token: (will be set after login)
```

### 3. Login Flow

1. Call `POST {{base_url}}/login`
2. Extract token from response
3. Set as environment variable
4. Use in subsequent requests

### 4. Example Request

```
Method: GET
URL: {{base_url}}/products
Headers:
  Authorization: Bearer {{token}}
```

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-01-01 | Initial API release |

---

## Support

For issues or questions:
- **Email**: info@rightmo.lk
- **Documentation**: See README.md
- **Tests**: Run `php artisan test` for test suite

---

**Last Updated**: January 1, 2026

**API Status**: ✅ Production Ready

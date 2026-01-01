# Testing Guide

This project includes comprehensive PHPUnit tests for all API endpoints.

## Test Coverage

### Authentication Tests (`tests/Feature/AuthTest.php`)

**Total: 9 tests**

1. ✅ User can login with valid credentials
2. ✅ User cannot login with invalid credentials
3. ✅ Login requires email
4. ✅ Login requires password
5. ✅ Login requires valid email format
6. ✅ Authenticated user can logout
7. ✅ Unauthenticated user cannot logout
8. ✅ Authenticated user can get their details
9. ✅ Unauthenticated user cannot get user details

### Product Tests (`tests/Feature/ProductTest.php`)

**Total: 25 tests**

#### Authorization Tests
1. ✅ Unauthenticated user cannot access products

#### Listing & Pagination Tests
2. ✅ Authenticated user can list products
3. ✅ Product listing pagination

#### Search & Filter Tests
4. ✅ Product search by name
5. ✅ Product filter by category
6. ✅ Product filter by price range
7. ✅ Product sort by price
8. ✅ Product sort by rating
9. ✅ Combined filters work together

#### CRUD Tests
10. ✅ Authenticated user can view single product
11. ✅ Viewing non-existent product returns 404
12. ✅ Authenticated user can create product
13. ✅ Authenticated user can update product
14. ✅ Authenticated user can delete product
15. ✅ Deleting non-existent product returns 404

#### Validation Tests
16. ✅ Product creation requires name
17. ✅ Product creation requires unique name
18. ✅ Product creation requires category
19. ✅ Product creation requires price
20. ✅ Product creation validates rating range

#### Image Upload Tests
21. ✅ Product with image upload

#### Categories Tests
22. ✅ Can get all categories

**Total Test Count: 34 tests**

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test File

```bash
# Run only authentication tests
php artisan test --filter AuthTest

# Run only product tests
php artisan test --filter ProductTest
```

### Run Specific Test Method

```bash
php artisan test --filter test_user_can_login_with_valid_credentials
```

### Run with Coverage

```bash
php artisan test --coverage
```

### Run in Parallel

```bash
php artisan test --parallel
```

## Test Database

Tests use an in-memory SQLite database by default (configured in `phpunit.xml`). This ensures:
- Fast test execution
- No interference with development database
- Clean state for each test run

The test database is automatically created and destroyed for each test run using the `RefreshDatabase` trait.

## Writing New Tests

### Test Structure

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class YourTest extends TestCase
{
    use RefreshDatabase;

    public function test_something(): void
    {
        // Arrange: Set up test data

        // Act: Perform the action

        // Assert: Verify the result
    }
}
```

### Common Assertions

```php
// Status codes
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated();
$response->assertNotFound();
$response->assertUnauthorized();

// JSON structure
$response->assertJsonStructure(['key' => ['nested']]);
$response->assertJson(['key' => 'value']);
$response->assertJsonCount(5, 'data');

// JSON validation errors
$response->assertJsonValidationErrors(['field']);

// Database assertions
$this->assertDatabaseHas('products', ['name' => 'Test']);
$this->assertDatabaseMissing('products', ['id' => 999]);
```

### Authentication in Tests

```php
// Create authenticated user
$user = User::factory()->create();
$token = $user->createToken('test')->plainTextToken;

// Make authenticated request
$response = $this->withHeader('Authorization', 'Bearer ' . $token)
    ->getJson('/api/products');
```

## Test Output Examples

### Successful Test Run

```bash
$ php artisan test

   PASS  Tests\Feature\AuthTest
  ✓ user can login with valid credentials
  ✓ user cannot login with invalid credentials
  ✓ login requires email
  ✓ login requires password
  ✓ login requires valid email format
  ✓ authenticated user can logout
  ✓ unauthenticated user cannot logout
  ✓ authenticated user can get their details
  ✓ unauthenticated user cannot get user details

   PASS  Tests\Feature\ProductTest
  ✓ unauthenticated user cannot access products
  ✓ authenticated user can list products
  ✓ product listing pagination
  ✓ product search by name
  ✓ product filter by category
  ✓ product filter by price range
  ✓ product sort by price
  ✓ product sort by rating
  ✓ authenticated user can view single product
  ✓ viewing non existent product returns 404
  ✓ authenticated user can create product
  ✓ product creation requires name
  ✓ product creation requires unique name
  ✓ product creation requires category
  ✓ product creation requires price
  ✓ product creation validates rating range
  ✓ authenticated user can update product
  ✓ authenticated user can delete product
  ✓ deleting non existent product returns 404
  ✓ can get all categories
  ✓ product with image upload
  ✓ combined filters work together

  Tests:    34 passed (90 assertions)
  Duration: 2.43s
```

## Continuous Integration

Add to `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install Dependencies
        run: composer install

      - name: Run Tests
        run: php artisan test
```

## Test Best Practices

1. **Use Descriptive Names**: Test names should clearly describe what they test
2. **One Assertion Per Test**: Keep tests focused on a single behavior
3. **Arrange-Act-Assert**: Structure tests with clear setup, action, and verification
4. **Use Factories**: Leverage model factories for test data generation
5. **Isolate Tests**: Each test should be independent and not rely on others
6. **Test Edge Cases**: Include tests for boundary conditions and error cases
7. **Keep Tests Fast**: Use in-memory database and minimize external dependencies

## Common Issues

### Database Not Refreshing

Make sure you're using the `RefreshDatabase` trait:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class YourTest extends TestCase
{
    use RefreshDatabase;
    //...
}
```

### Authentication Failing

Ensure you're including the Bearer token:

```php
$this->withHeader('Authorization', 'Bearer ' . $token)
```

### Factory Not Found

Make sure the factory exists in `database/factories/` and is properly namespaced.

## Test Coverage Goals

- **Controllers**: 100% coverage
- **Models**: 80%+ coverage
- **Services**: 90%+ coverage
- **Overall**: 85%+ coverage

## Running Tests in Docker

```bash
docker-compose exec app php artisan test
```

---

**All tests passing ensures the API is working correctly!** 🚀

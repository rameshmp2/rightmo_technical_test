<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create authenticated user
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test unauthenticated user cannot access products
     */
    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can list products
     */
    public function test_authenticated_user_can_list_products(): void
    {
        // Create some products
        Product::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'category', 'price', 'rating', 'image', 'description', 'created_at', 'updated_at']
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);

        $this->assertEquals(5, $response->json('total'));
    }

    /**
     * Test product listing pagination
     */
    public function test_product_listing_pagination(): void
    {
        Product::factory()->count(25)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(25, $response->json('total'));
        $this->assertEquals(3, $response->json('last_page'));
    }

    /**
     * Test product search by name
     */
    public function test_product_search_by_name(): void
    {
        Product::factory()->create(['name' => 'Laptop Pro 15']);
        Product::factory()->create(['name' => 'Wireless Mouse']);
        Product::factory()->create(['name' => 'Laptop Air 13']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?search=Laptop');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('total'));
    }

    /**
     * Test product filter by category
     */
    public function test_product_filter_by_category(): void
    {
        Product::factory()->count(3)->create(['category' => 'Electronics']);
        Product::factory()->count(2)->create(['category' => 'Furniture']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?category=Electronics');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test product filter by price range
     */
    public function test_product_filter_by_price_range(): void
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 150.00]);
        Product::factory()->create(['price' => 250.00]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?min_price=100&max_price=200');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
    }

    /**
     * Test product sort by price
     */
    public function test_product_sort_by_price(): void
    {
        Product::factory()->create(['name' => 'Product A', 'price' => 300.00]);
        Product::factory()->create(['name' => 'Product B', 'price' => 100.00]);
        Product::factory()->create(['name' => 'Product C', 'price' => 200.00]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?sort_by=price&sort_order=asc');

        $response->assertStatus(200);
        $products = $response->json('data');
        $this->assertEquals(100.00, $products[0]['price']);
        $this->assertEquals(200.00, $products[1]['price']);
        $this->assertEquals(300.00, $products[2]['price']);
    }

    /**
     * Test product sort by rating
     */
    public function test_product_sort_by_rating(): void
    {
        Product::factory()->create(['rating' => 4.5]);
        Product::factory()->create(['rating' => 3.0]);
        Product::factory()->create(['rating' => 5.0]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?sort_by=rating&sort_order=desc');

        $response->assertStatus(200);
        $products = $response->json('data');
        $this->assertEquals(5.0, $products[0]['rating']);
        $this->assertEquals(4.5, $products[1]['rating']);
        $this->assertEquals(3.0, $products[2]['rating']);
    }

    /**
     * Test authenticated user can view single product
     */
    public function test_authenticated_user_can_view_single_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'product' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'price' => '99.99',
                ],
            ]);
    }

    /**
     * Test viewing non-existent product returns 404
     */
    public function test_viewing_non_existent_product_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }

    /**
     * Test authenticated user can create product
     */
    public function test_authenticated_user_can_create_product(): void
    {
        $productData = [
            'name' => 'New Product',
            'category' => 'Electronics',
            'price' => 199.99,
            'rating' => 4.5,
            'description' => 'A great product',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Product created successfully',
                'product' => [
                    'name' => 'New Product',
                    'category' => 'Electronics',
                    'price' => '199.99',
                    'rating' => '4.50',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'category' => 'Electronics',
        ]);
    }

    /**
     * Test product creation requires name
     */
    public function test_product_creation_requires_name(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'category' => 'Electronics',
                'price' => 99.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test product creation requires unique name
     */
    public function test_product_creation_requires_unique_name(): void
    {
        Product::factory()->create(['name' => 'Existing Product']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Existing Product',
                'category' => 'Electronics',
                'price' => 99.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test product creation requires category
     */
    public function test_product_creation_requires_category(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'price' => 99.99,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    /**
     * Test product creation requires price
     */
    public function test_product_creation_requires_price(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'category' => 'Electronics',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /**
     * Test product creation validates rating range
     */
    public function test_product_creation_validates_rating_range(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'category' => 'Electronics',
                'price' => 99.99,
                'rating' => 6.0, // Invalid: max is 5.0
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    /**
     * Test authenticated user can update product
     */
    public function test_authenticated_user_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 99.99,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/products/' . $product->id, [
                'name' => 'Updated Name',
                'price' => 149.99,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated successfully',
                'product' => [
                    'name' => 'Updated Name',
                    'price' => '149.99',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 149.99,
        ]);
    }

    /**
     * Test authenticated user can delete product
     */
    public function test_authenticated_user_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test deleting non-existent product returns 404
     */
    public function test_deleting_non_existent_product_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/products/9999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found',
            ]);
    }

    /**
     * Test can get all categories
     */
    public function test_can_get_all_categories(): void
    {
        Product::factory()->create(['category' => 'Electronics']);
        Product::factory()->create(['category' => 'Furniture']);
        Product::factory()->create(['category' => 'Electronics']); // Duplicate

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['categories'])
            ->assertJsonCount(2, 'categories');
    }

    /**
     * Test product with image upload
     */
    public function test_product_with_image_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('product.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', [
                'name' => 'Product with Image',
                'category' => 'Electronics',
                'price' => 99.99,
                'image' => $file,
            ]);

        $response->assertStatus(201);

        // Assert the file was stored
        $product = Product::where('name', 'Product with Image')->first();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    /**
     * Test combined filters work together
     */
    public function test_combined_filters_work_together(): void
    {
        Product::factory()->create([
            'name' => 'Laptop Pro',
            'category' => 'Electronics',
            'price' => 1200.00,
        ]);
        Product::factory()->create([
            'name' => 'Office Chair',
            'category' => 'Furniture',
            'price' => 300.00,
        ]);
        Product::factory()->create([
            'name' => 'Laptop Air',
            'category' => 'Electronics',
            'price' => 800.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/products?search=Laptop&category=Electronics&min_price=900');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals('Laptop Pro', $response->json('data')[0]['name']);
    }
}

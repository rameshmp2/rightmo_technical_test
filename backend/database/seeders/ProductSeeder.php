<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories first
        $categories = [
            'Electronics',
            'Furniture',
            'Appliances',
            'Sports',
        ];

        $categoryIds = [];
        foreach ($categories as $categoryName) {
            $category = Category::firstOrCreate(['name' => $categoryName]);
            $categoryIds[$categoryName] = $category->id;
        }

        // Create products with category_id
        $products = [
            [
                'name' => 'Laptop Pro 15',
                'category_id' => $categoryIds['Electronics'],
                'price' => 1299.99,
                'rating' => 4.5,
                'description' => 'High-performance laptop with 16GB RAM and 512GB SSD',
                'image' => null,
            ],
            [
                'name' => 'Wireless Mouse',
                'category_id' => $categoryIds['Electronics'],
                'price' => 29.99,
                'rating' => 4.2,
                'description' => 'Ergonomic wireless mouse with precision tracking',
                'image' => null,
            ],
            [
                'name' => 'Office Chair',
                'category_id' => $categoryIds['Furniture'],
                'price' => 249.99,
                'rating' => 4.7,
                'description' => 'Comfortable ergonomic office chair with lumbar support',
                'image' => null,
            ],
            [
                'name' => 'Desk Lamp LED',
                'category_id' => $categoryIds['Furniture'],
                'price' => 45.99,
                'rating' => 4.3,
                'description' => 'Adjustable LED desk lamp with touch controls',
                'image' => null,
            ],
            [
                'name' => 'Coffee Maker',
                'category_id' => $categoryIds['Appliances'],
                'price' => 89.99,
                'rating' => 4.6,
                'description' => 'Programmable coffee maker with 12-cup capacity',
                'image' => null,
            ],
            [
                'name' => 'Blender Pro',
                'category_id' => $categoryIds['Appliances'],
                'price' => 129.99,
                'rating' => 4.4,
                'description' => 'Powerful blender for smoothies and food processing',
                'image' => null,
            ],
            [
                'name' => 'Running Shoes',
                'category_id' => $categoryIds['Sports'],
                'price' => 79.99,
                'rating' => 4.5,
                'description' => 'Lightweight running shoes with superior cushioning',
                'image' => null,
            ],
            [
                'name' => 'Yoga Mat',
                'category_id' => $categoryIds['Sports'],
                'price' => 24.99,
                'rating' => 4.8,
                'description' => 'Non-slip yoga mat with extra thickness',
                'image' => null,
            ],
            [
                'name' => 'Smartphone X12',
                'category_id' => $categoryIds['Electronics'],
                'price' => 899.99,
                'rating' => 4.6,
                'description' => '5G smartphone with 128GB storage and triple camera',
                'image' => null,
            ],
            [
                'name' => 'Bluetooth Speaker',
                'category_id' => $categoryIds['Electronics'],
                'price' => 59.99,
                'rating' => 4.4,
                'description' => 'Portable Bluetooth speaker with 360-degree sound',
                'image' => null,
            ],
            [
                'name' => 'Standing Desk',
                'category_id' => $categoryIds['Furniture'],
                'price' => 399.99,
                'rating' => 4.7,
                'description' => 'Adjustable standing desk with electric height control',
                'image' => null,
            ],
            [
                'name' => 'Bookshelf',
                'category_id' => $categoryIds['Furniture'],
                'price' => 149.99,
                'rating' => 4.3,
                'description' => '5-tier wooden bookshelf with modern design',
                'image' => null,
            ],
            [
                'name' => 'Microwave Oven',
                'category_id' => $categoryIds['Appliances'],
                'price' => 119.99,
                'rating' => 4.5,
                'description' => '1000W microwave oven with 10 power levels',
                'image' => null,
            ],
            [
                'name' => 'Air Fryer',
                'category_id' => $categoryIds['Appliances'],
                'price' => 99.99,
                'rating' => 4.7,
                'description' => 'Digital air fryer with 8 preset cooking functions',
                'image' => null,
            ],
            [
                'name' => 'Dumbbell Set',
                'category_id' => $categoryIds['Sports'],
                'price' => 149.99,
                'rating' => 4.6,
                'description' => 'Adjustable dumbbell set 5-50 lbs',
                'image' => null,
            ],
            [
                'name' => 'Exercise Bike',
                'category_id' => $categoryIds['Sports'],
                'price' => 299.99,
                'rating' => 4.4,
                'description' => 'Stationary exercise bike with digital monitor',
                'image' => null,
            ],
            [
                'name' => 'Tablet Pro 11',
                'category_id' => $categoryIds['Electronics'],
                'price' => 649.99,
                'rating' => 4.5,
                'description' => '11-inch tablet with stylus support and 256GB storage',
                'image' => null,
            ],
            [
                'name' => 'Keyboard Mechanical',
                'category_id' => $categoryIds['Electronics'],
                'price' => 89.99,
                'rating' => 4.6,
                'description' => 'RGB mechanical keyboard with blue switches',
                'image' => null,
            ],
            [
                'name' => 'Monitor 27 inch',
                'category_id' => $categoryIds['Electronics'],
                'price' => 279.99,
                'rating' => 4.7,
                'description' => '27-inch 4K monitor with HDR support',
                'image' => null,
            ],
            [
                'name' => 'Gaming Chair',
                'category_id' => $categoryIds['Furniture'],
                'price' => 199.99,
                'rating' => 4.5,
                'description' => 'Ergonomic gaming chair with adjustable armrests',
                'image' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

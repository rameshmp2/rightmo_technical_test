<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Product name must be unique
            $table->string('category');
            $table->decimal('price', 10, 2); // Price with 2 decimal places
            $table->decimal('rating', 3, 2)->default(0.00); // Rating from 0.00 to 5.00
            $table->string('image')->nullable(); // Image path/URL
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

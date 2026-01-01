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
        Schema::table('products', function (Blueprint $table) {
            // Add category_id as foreign key
            $table->unsignedBigInteger('category_id')->nullable()->after('name');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            // Keep the old category column for backward compatibility during migration
            // We'll populate category_id based on category name, then can drop category later if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};

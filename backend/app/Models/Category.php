<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the products for this category
     */
    public function products()
    {
        // Use the proper foreign key relationship
        return $this->hasMany(Product::class, 'category_id');
    }
}

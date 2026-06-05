<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'buy_price',
        'price',
        'stock',
        'is_new',
        'bestseller',
        'status',
        'image_url',
    ];

    protected $casts = [
        'is_new' => 'boolean',
        'bestseller' => 'boolean',
        'price' => 'decimal:2',
        'buy_price' => 'decimal:2',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
        public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('approved', true);
    }

    public function averageRating()
    {
        return $this->approvedReviews()->avg('rating');
    }
    
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id');
    }
    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function colorVariations()
    {
        return $this->variations()->where('attribute_name', 'color');
    }
}
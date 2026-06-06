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
        'image_url',   // kept for backwards-compat (thumbnails in product cards)
    ];

    protected $casts = [
        'is_new'     => 'boolean',
        'bestseller' => 'boolean',
        'price'      => 'decimal:2',
        'buy_price'  => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('approved', true);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id');
    }

    /** All gallery images ordered by sort_order */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** All color swatches ordered by sort_order */
    public function colors()
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    // ── Helpers ───────────────────────────────────────────

    /** The primary gallery image (or first if none marked primary) */
    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();
    }

    /** Convenience: returns the URL string of the primary image */
    public function getMainImageUrlAttribute(): string
    {
        $img = $this->primaryImage();
        if ($img) return $img->url;
        return $this->image_url ? asset($this->image_url) : '';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function averageRating()
    {
        return $this->approvedReviews()->avg('rating');
    }
}
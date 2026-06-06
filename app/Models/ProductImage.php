<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(ProductColor::class, 'color_id');
    }

    // ── Accessors ─────────────────────────────────────────

    /**
     * Full public URL: asset('storage/products/abc.jpg')
     */
    public function getUrlAttribute(): string
    {
        return asset($this->image_path);
    }
}
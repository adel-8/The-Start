<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'name_ar',
        'hex_code',
        'sort_order',
    ];

    // ── Relationships ─────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'color_id')->orderBy('sort_order');
    }

    // ── Accessors ─────────────────────────────────────────

    /**
     * Return Arabic name if locale is Arabic and name_ar exists, otherwise English name.
     */
    public function getDisplayNameAttribute(): string
    {
        return (app()->getLocale() === 'ar' && $this->name_ar)
            ? $this->name_ar
            : $this->name;
    }
}
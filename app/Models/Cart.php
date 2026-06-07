<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
    'user_id', 'cart_key', 'product_id', 'color_id',
    'quantity', 'product_name', 'price', 'image_path', 'color_name'
];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
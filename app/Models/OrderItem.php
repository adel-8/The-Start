<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'product_id',
        'variation_id',
        'quantity',
        'price_at_purchase',
        'color_id', 
    ];

    protected $casts = [
        'price_at_purchase' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // app/Models/OrderItem.php

    public function color()
    {
        return $this->belongsTo(ProductColor::class, 'color_id');
    }
}
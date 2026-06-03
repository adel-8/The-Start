<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Order extends Model
{
    use HasFactory;
    protected $fillable = [
    'order_number',
    'user_id',
    'guest_name',
    'guest_email',
    'guest_phone',
    'shipping_address_id',
    'billing_address_id',
    'coupon_id',
    'total_price',
    'shipping_cost',
    'status',
    'payment_method',
    'payment_status',
    'stripe_session_id',
    'tracking_number',
    'notes',
    'payment_proof', 
];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
        public function getRouteKeyName()
    {
        return 'order_number';
    }

}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart()
    {
        return Session::get('cart', []);
    }

    public function clearCart()
    {
        Session::forget('cart');
    }

    public function isEmpty()
    {
        return empty($this->getCart());
    }

    public function calculateSubtotal()
    {
        $cart = $this->getCart();
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }
}
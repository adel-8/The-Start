<?php

namespace App\Traits;

use Illuminate\Support\Facades\Session;

trait CartHelper
{
    /**
     * Get current cart (always from session, no DB persistence).
     * This allows composite keys like "123_5" for product colors.
     */
    protected function getCart()
    {
        return Session::get('cart', []);
    }

    /**
     * Save cart to session.
     */
    protected function saveCart($cart)
    {
        Session::put('cart', $cart);
    }

    /**
     * Get total number of items in cart.
     */
    protected function getCartCount()
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }
}
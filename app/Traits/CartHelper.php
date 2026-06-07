<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait CartHelper
{
    /**
     * Get the current cart (from database if logged in, otherwise from session).
     * Returns an array with the same structure as the session cart.
     */
    protected function getCart()
    {
        if (Auth::check()) {
            $cart = [];
            $dbCart = Cart::where('user_id', Auth::id())->get();
            foreach ($dbCart as $item) {
                $cart[$item->cart_key] = [
                    'cart_key'   => $item->cart_key,
                    'product_id' => $item->product_id,
                    'name'       => $item->product_name,
                    'price'      => (float) $item->price,
                    'quantity'   => $item->quantity,
                    'image'      => $item->image_path,
                    'color_id'   => $item->color_id,
                    'color_name' => $item->color_name,
                ];
            }
            return $cart;
        }
        return Session::get('cart', []);
    }

    /**
     * Save the entire cart (for logged‑in users: sync database; for guests: store in session).
     */
    protected function saveCart($cart)
    {
        if (Auth::check()) {
            // Get all existing cart keys for this user
            $existingKeys = Cart::where('user_id', Auth::id())->pluck('cart_key')->toArray();
            $newKeys = array_keys($cart);

            // Delete items that are no longer in the cart
            $toDelete = array_diff($existingKeys, $newKeys);
            if (!empty($toDelete)) {
                Cart::where('user_id', Auth::id())->whereIn('cart_key', $toDelete)->delete();
            }

            // Update or create each cart item
            foreach ($cart as $cartKey => $item) {
                Cart::updateOrCreate(
                    [
                        'user_id'  => Auth::id(),
                        'cart_key' => $cartKey,
                    ],
                    [
                        'product_id'   => $item['product_id'],
                        'color_id'     => $item['color_id'] ?? null,
                        'quantity'     => $item['quantity'],
                        'product_name' => $item['name'],
                        'price'        => $item['price'],
                        'image_path'   => $item['image'],
                        'color_name'   => $item['color_name'] ?? null,
                    ]
                );
            }
        } else {
            Session::put('cart', $cart);
        }
    }

    /**
     * Get total number of items in the cart.
     */
    protected function getCartCount()
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }
}
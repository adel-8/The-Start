<?php

namespace App\Traits;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait CartHelper
{
    protected function getCart()
    {
        if (Auth::check()) {
            $cart = [];
            $dbCart = Cart::where('user_id', Auth::id())->with('product')->get();
            foreach ($dbCart as $item) {
                $cart[$item->product_id] = [
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image_url,
                ];
            }
            return $cart;
        }
        return Session::get('cart', []);
    }

    protected function saveCart($cart)
    {
        if (Auth::check()) {
            foreach ($cart as $productId => $item) {
                Cart::updateOrCreate(
                    ['user_id' => Auth::id(), 'product_id' => $productId],
                    ['quantity' => $item['quantity']]
                );
            }
            $ids = array_keys($cart);
            Cart::where('user_id', Auth::id())->whereNotIn('product_id', $ids)->delete();
        } else {
            Session::put('cart', $cart);
        }
    }

    protected function getCartCount()
    {
        $cart = $this->getCart();
        return array_sum(array_column($cart, 'quantity'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Traits\CartHelper;

class CartController extends Controller
{
    use CartHelper;

    /**
     * Display the cart page.
     */
    public function cart()
    {
        $cart = $this->getCart();
        return view('cart', compact('cart'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        $cart = $this->getCart();

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'name'     => $product->name,
                'price'    => $product->price,
                'quantity' => $quantity,
                'image'    => $product->image_url,
            ];
        }

        $this->saveCart($cart);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Product added to cart',
                'cart_count' => $this->getCartCount()
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart');
    }

    /**
     * Update product quantity.
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $cart = $this->getCart();
        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] = $request->quantity;
            $this->saveCart($cart);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cart');
    }

    /**
     * Remove a product from the cart.
     */
    public function remove($id, Request $request)
    {
        $cart = $this->getCart();
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->saveCart($cart);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cart');
    }

    /**
     * Clear the entire cart.
     */
    public function clear(Request $request)
    {
        $this->saveCart([]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cart');
    }

    /**
     * Get the current cart count (for AJAX).
     */
    public function count()
    {
        return response()->json(['count' => $this->getCartCount()]);
    }
}
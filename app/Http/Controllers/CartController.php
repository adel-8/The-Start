<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use App\Models\ProductColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Traits\CartHelper;

class CartController extends Controller
{
    use CartHelper;

    // ── Display ───────────────────────────────────────────

    public function cart()
    {
        $cart = $this->getCart();
        return view('cart', compact('cart'));
    }

    // ── Add ───────────────────────────────────────────────

    public function add(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'nullable|integer|min:1',
        'color_id'   => 'nullable|exists:product_colors,id',
    ]);

    $product = Product::with(['images', 'colors'])->findOrFail($request->product_id);
    $quantity = max(1, (int) $request->quantity);
    $colorId = $request->color_id ? (int) $request->color_id : null;

    // ── Check stock before adding to cart ──
    if ($product->stock < $quantity) {
        $errorMsg = __('messages.insufficient_stock', ['stock' => $product->stock]);
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        }
        return redirect()->back()->withErrors(['stock' => $errorMsg]);
    }

    // If product has colors, a color must be chosen
    if ($product->colors->isNotEmpty() && !$colorId) {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => __('messages.please_select_color')], 422);
        }
        return redirect()->back()->withErrors(['color' => __('messages.please_select_color')]);
    }

    // Validate chosen color belongs to this product
    if ($colorId && !$product->colors->contains('id', $colorId)) {
        $colorId = null;
    }

    // Cart key
    $cartKey = $colorId ? "{$product->id}_{$colorId}" : (string) $product->id;
    $colorName = $colorId ? $product->colors->firstWhere('id', $colorId)?->display_name : null;

    // Best image
    $image = $product->image_url;
    if ($colorId) {
        $colorImg = $product->images->firstWhere('color_id', $colorId);
        if ($colorImg) $image = $colorImg->image_path;
    } else {
        $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        if ($primaryImg) $image = $primaryImg->image_path;
    }

    $cart = $this->getCart();

    if (isset($cart[$cartKey])) {
        $newQty = $cart[$cartKey]['quantity'] + $quantity;
        // Re-check stock for total quantity
        if ($product->stock < $newQty) {
            $errorMsg = __('messages.insufficient_stock_total', ['stock' => $product->stock]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withErrors(['stock' => $errorMsg]);
        }
        $cart[$cartKey]['quantity'] = $newQty;
    } else {
        $cart[$cartKey] = [
            'cart_key'   => $cartKey,
            'product_id' => $product->id,
            'name'       => $product->name,
            'price'      => (float) $product->price,
            'quantity'   => $quantity,
            'image'      => $image,
            'color_id'   => $colorId,
            'color_name' => $colorName,
        ];
    }

    $this->saveCart($cart);

    if ($request->expectsJson()) {
        return response()->json([
            'success'    => true,
            'message'    => __('messages.product_added_to_cart'),
            'cart_count' => $this->getCartCount(),
        ]);
    }

    return redirect()->back()->with('success', __('messages.product_added_to_cart'));
}
    // ── Update ────────────────────────────────────────────

    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart();

        if (isset($cart[$request->cart_key])) {
            $cart[$request->cart_key]['quantity'] = $request->quantity;
            $this->saveCart($cart);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cart');
    }

    // ── Remove ────────────────────────────────────────────

    /**
     * Route: DELETE /cart/remove/{id}
     * {id} is the cart_key, e.g. "5" or "5_3"
     */
    public function remove(string $id, Request $request)
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

    // ── Clear ─────────────────────────────────────────────

    public function clear(Request $request)
    {
        $this->saveCart([]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cart');
    }

    // ── Count (AJAX) ──────────────────────────────────────

    public function count()
    {
        return response()->json(['count' => $this->getCartCount()]);
    }
}
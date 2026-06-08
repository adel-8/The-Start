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

        // ── Live stock check ──────────────────────────────
        // Attach real-time stock to every cart item so the
        // view can warn the user before they hit checkout.
        if (!empty($cart)) {
            $productIds = array_column($cart, 'product_id');
            $liveStock  = Product::whereIn('id', $productIds)
                ->pluck('stock', 'id');          // [id => stock]

            foreach ($cart as $key => &$item) {
                $stock = $liveStock[$item['product_id']] ?? 0;
                $item['live_stock']    = $stock;
                $item['out_of_stock']  = $stock <= 0;
                $item['low_stock']     = $stock > 0 && $stock < 5;
                // Cap stored qty to available stock so subtotal is accurate
                if ($stock > 0 && $item['quantity'] > $stock) {
                    $item['quantity'] = $stock;
                    // Persist the corrected quantity
                    $this->updateItemQuantity($key, $stock);
                }
            }
            unset($item);
        }

        return view('cart', compact('cart'));
    }

    // ── Add ───────────────────────────────────────────────

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1|max:50',
            'color_id'   => 'nullable|exists:product_colors,id',
        ]);

        $product  = Product::with(['images', 'colors'])->findOrFail($request->product_id);
        $quantity = max(1, (int) $request->quantity);
        $colorId  = $request->color_id ? (int) $request->color_id : null;

        // ── Stock check ───────────────────────────────────
        if ($product->stock <= 0) {
            $msg = __('messages.out_of_stock');
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : redirect()->back()->withErrors(['stock' => $msg]);
        }

        if ($product->stock < $quantity) {
            $msg = __('messages.insufficient_stock', ['stock' => $product->stock]);
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : redirect()->back()->withErrors(['stock' => $msg]);
        }

        // ── Color validation ──────────────────────────────
        if ($product->colors->isNotEmpty() && !$colorId) {
            $msg = __('messages.please_select_color');
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : redirect()->back()->withErrors(['color' => $msg]);
        }

        if ($colorId && !$product->colors->contains('id', $colorId)) {
            $colorId = null;
        }

        // ── Build cart key & resolve image ───────────────
        $cartKey   = $colorId ? "{$product->id}_{$colorId}" : (string) $product->id;
        $colorName = $colorId ? $product->colors->firstWhere('id', $colorId)?->display_name : null;

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
            if ($product->stock < $newQty) {
                $msg = __('messages.insufficient_stock_total', ['stock' => $product->stock]);
                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : redirect()->back()->withErrors(['stock' => $msg]);
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

        return $request->expectsJson()
            ? response()->json([
                'success'    => true,
                'message'    => __('messages.product_added_to_cart'),
                'cart_count' => $this->getCartCount(),
            ])
            : redirect()->back()->with('success', __('messages.product_added_to_cart'));
    }

    // ── Update ────────────────────────────────────────────

    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $cart = $this->getCart();

        if (!isset($cart[$request->cart_key])) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Item not found'], 404)
                : redirect()->route('cart');
        }

        // ── Live stock check on update ────────────────────
        $productId = $cart[$request->cart_key]['product_id'];
        $product   = Product::find($productId);

        if ($product && $product->stock < $request->quantity) {
            $msg = __('messages.insufficient_stock', ['stock' => $product->stock]);
            return $request->expectsJson()
                ? response()->json([
                    'success'      => false,
                    'message'      => $msg,
                    'capped_qty'   => $product->stock,   // let JS update the input
                ], 422)
                : redirect()->route('cart')->with('error', $msg);
        }

        $cart[$request->cart_key]['quantity'] = $request->quantity;
        $this->saveCart($cart);

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : redirect()->route('cart');
    }

    // ── Remove ────────────────────────────────────────────

    public function remove(string $id, Request $request)
    {
        $cart = $this->getCart();

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $this->saveCart($cart);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : redirect()->route('cart');
    }

    // ── Clear ─────────────────────────────────────────────

    public function clear(Request $request)
    {
        $this->saveCart([]);

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : redirect()->route('cart');
    }

    // ── Count (AJAX) ──────────────────────────────────────

    public function count()
    {
        return response()->json(['count' => $this->getCartCount()]);
    }

    // ── Private helpers ───────────────────────────────────

    /**
     * Update a single item's quantity without reloading the whole cart.
     * Used internally to cap quantities when stock changes.
     */
    private function updateItemQuantity(string $cartKey, int $qty): void
    {
        $cart = $this->getCart();
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $qty;
            $this->saveCart($cart);
        }
    }
}
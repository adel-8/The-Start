<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('signin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Merge guest cart into user's cart
            $sessionCart = session('cart', []);
            if (!empty($sessionCart)) {
                $userCart = Cart::where('user_id', Auth::id())->get()->keyBy('cart_key');
                foreach ($sessionCart as $cartKey => $item) {
                    if ($userCart->has($cartKey)) {
                        $userCart[$cartKey]->update(['quantity' => $userCart[$cartKey]->quantity + $item['quantity']]);
                    } else {
                        Cart::create([
                            'user_id'      => Auth::id(),
                            'cart_key'     => $cartKey,
                            'product_id'   => $item['product_id'],
                            'color_id'     => $item['color_id'] ?? null,
                            'quantity'     => $item['quantity'],
                            'product_name' => $item['name'],
                            'price'        => $item['price'],
                            'image_path'   => $item['image'],
                            'color_name'   => $item['color_name'] ?? null,
                        ]);
                    }
                }
                session()->forget('cart');
            }

            // Redirect to intended page or home
            return redirect()->intended(route('home'));
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('signin');
    }
}
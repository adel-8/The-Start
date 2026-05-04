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
            $request->session()->regenerate(); // security

            // ---- Merge guest cart into user's cart ----
            $sessionCart = Session::get('cart', []);
            foreach ($sessionCart as $productId => $item) {
                $cartItem = Cart::where('user_id', Auth::id())
                                ->where('product_id', $productId)
                                ->first();
                if ($cartItem) {
                    $cartItem->quantity += $item['quantity'];
                    $cartItem->save();
                } else {
                    Cart::create([
                        'user_id'    => Auth::id(),
                        'product_id' => $productId,
                        'quantity'   => $item['quantity'],
                    ]);
                }
            }
            Session::forget('cart');
            // -------------------------------------------

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('signin');
    }
}
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserOrderController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/Shop', [ShopController::class, 'Shop'])->name('Shop');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/about', [AboutController::class, 'about'])->name('about');
// Legal pages (using settings from database)


/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'cart'])->name('cart');
    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/count', [CartController::class, 'count'])->name('cart.count');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('/signin', [LoginController::class, 'showLoginForm'])->name('signin');
Route::post('/signin', [LoginController::class, 'login'])->middleware('throttle:5,1');

Route::get('/signup', [RegisterController::class, 'showSignupForm'])->name('signup');
Route::post('/signup', [RegisterController::class, 'register'])->middleware('throttle:signup');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Fix for Laravel default redirect
Route::get('/login', fn () => redirect()->route('signin'))->name('login');

// Password reset
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:forgot_password');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// Email verification
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->email))) {
        abort(403);
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()->route('signin')->with('success', 'Your email is already verified. Please sign in.');
    }

    $user->markEmailAsVerified();
    event(new Verified($user));

    return redirect()->route('signin')->with('success', 'Your email has been verified. You can now sign in.');
})->middleware('signed')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if (! $request->user()) {
        return redirect()->route('signin');
    }

    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'A fresh verification link has been sent to your email address.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Checkout (guest allowed)
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');

// Checkout success page (kept here so named routes are near checkout)
Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

// Coupon (throttled)
Route::post('/coupon/apply', [CheckoutController::class, 'applyCoupon'])
    ->name('coupon.apply')
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Orders (protected)
|--------------------------------------------------------------------------
*/

Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');

/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // AJAX data endpoint for charts
    Route::get('/analytics/data', [DashboardController::class, 'getAnalyticsData'])->name('analytics.data');

    // Products
    Route::resource('products', AdminProductController::class);
    Route::delete('/products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulk-delete');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::delete('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::get('/categories/{category}/move/{direction}', [CategoryController::class, 'move'])->name('categories.move');

    // Orders
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    Route::post('/orders/{order}/payment', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.payment.update');
    // Secure access to payment proofs (stored on local disk)
    Route::get('/orders/{order}/proof', [AdminOrderController::class, 'proof'])->name('orders.proof');
    
    // Coupons
    Route::resource('coupons', CouponController::class);
    Route::delete('/coupons/bulk-delete', [CouponController::class, 'bulkDelete'])->name('coupons.bulk-delete');

    // Banners
    Route::resource('banners', BannerController::class);
    Route::delete('/banners/bulk-delete', [BannerController::class, 'bulkDelete'])->name('banners.bulk-delete');
    Route::get('/banners/{banner}/move/{direction}', [BannerController::class, 'move'])->name('banners.move');

    // Contact messages
    Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);

    // Reviews
    Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class)->only(['index', 'destroy']);
    Route::post('/reviews/{review}/approve', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');

    // Settings (visible to admins, but update restricted to owner)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Owner-only routes
    Route::middleware('owner')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    });
});

/*
|--------------------------------------------------------------------------
| Banner Click Tracking (Public)
|--------------------------------------------------------------------------
*/

Route::get('/banner/click/{id}', [App\Http\Controllers\Admin\BannerController::class, 'trackClick'])->name('banner.click');

/*
|--------------------------------------------------------------------------
| User Profile & Orders
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/account/orders', [UserOrderController::class, 'index'])->middleware('verified')->name('orders.index');
});

/*
|--------------------------------------------------------------------------
| Contact (public)
|--------------------------------------------------------------------------
*/

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:contact');

/*
|--------------------------------------------------------------------------
| Google OAuth
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| Addresses (auth)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::resource('addresses', AddressController::class)->except(['show']);
    Route::patch('addresses/{address}/set-default', [AddressController::class, 'setDefault'])->name('addresses.set-default');
});

/*
|--------------------------------------------------------------------------
| Product Reviews (auth)
|--------------------------------------------------------------------------
*/

Route::post('/product/{product}/review', [ReviewController::class, 'store'])->name('product.review.store')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Stripe Payments
|--------------------------------------------------------------------------
*/

Route::match(['get', 'post'], '/stripe/checkout', [StripeController::class, 'checkout'])->middleware('throttle:3,1')->name('stripe.checkout');
Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
// Stripe webhook endpoint (Stripe will POST here)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| BaridiMob (Algerian offline payment)
|--------------------------------------------------------------------------
*/

Route::get('/payment/baridimob', [PaymentController::class, 'baridimobInstructions'])->name('payment.baridimob');
Route::post('/payment/baridimob/upload', [PaymentController::class, 'uploadProof'])->middleware('throttle:3,1')->name('payment.baridimob.upload');

/*
|--------------------------------------------------------------------------
| Locale Switcher
|--------------------------------------------------------------------------
*/

Route::get('/locale/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'ar'])) {
        abort(400);
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('locale');


// Legal pages (using settings from database)
Route::get('/terms', [App\Http\Controllers\PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [App\Http\Controllers\PageController::class, 'privacy'])->name('privacy');
Route::get('/return-policy', [App\Http\Controllers\PageController::class, 'returnPolicy'])->name('return.policy');
Route::get('/shipping-policy', [App\Http\Controllers\PageController::class, 'shippingPolicy'])->name('shipping.policy');

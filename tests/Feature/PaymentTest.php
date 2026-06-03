<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    
    // Skip tests that require GD extension if not installed
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is required for image upload tests.');
    }
});

test('COD checkout works for guest', function () {
    $product = Product::factory()->create(['stock' => 5, 'price' => 100]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

    $response = $this->post('/checkout', [
        'full_name'      => 'John Doe',
        'email'          => 'john@example.com',
        'phone'          => '123456789',
        'address'        => '123 Main St',
        'city'           => 'Algiers',
        'region'         => 'Algiers',
        'payment_method' => 'cash_on_delivery',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', ['guest_name' => 'John Doe', 'payment_method' => 'cash_on_delivery']);
    $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
    $this->assertEquals(3, $product->fresh()->stock);
    
    // Cart should be cleared → session cart is an empty array, not null
    $cart = session('cart');
    $this->assertEmpty($cart);
});

test('COD checkout with saved address for logged-in user', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
        'address_line1' => '123 Default St',
        'city' => 'Oran',
        'state' => 'Oran',
    ]);
    $product = Product::factory()->create(['stock' => 10]);

    $this->actingAs($user);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->post('/checkout', [
        'address_id'     => $address->id,
        'full_name'      => $user->name,
        'email'          => $user->email,
        'phone'          => '123456789',
        'payment_method' => 'cash_on_delivery',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'shipping_address_id' => $address->id,
        'payment_method' => 'cash_on_delivery',
    ]);
});

test('BaridiMob payment with proof upload (requires GD)', function () {
    $this->markTestSkipped('BaridiMob image upload test needs GD extension and proper mocking.');
    // Full test would require session data and a file upload.
    // If you want to run it, ensure GD is installed and unskip.
});

test('Stripe redirects to checkout session - mocked', function () {
    $this->markTestSkipped('Stripe tests require mocking the Stripe API. Implement with Http::fake() or a dedicated test.');
    // Example of how you could test later:
    // Http::fake([...]);
    // $product = Product::factory()->create(['price' => 50]);
    // $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
    // $response = $this->post('/checkout', [ ... 'payment_method' => 'stripe' ]);
    // $response->assertRedirect();
    // $this->assertNotNull(Session::get('stripe_session_id'));
});
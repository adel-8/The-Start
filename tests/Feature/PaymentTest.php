<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;


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


test('BaridiMob payment with proof upload', function () {
    Storage::fake('local');
    $product = Product::factory()->create(['stock' => 10]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    // First submit checkout with baridimob
    $response = $this->post('/checkout', [
        'full_name'      => 'Jane Doe',
        'email'          => 'jane@example.com',
        'phone'          => '987654321',
        'address'        => '456 Elm St',
        'city'           => 'Oran',
        'region'         => 'Oran',
        'payment_method' => 'baridimob',
    ]);
    $response->assertRedirect(route('payment.baridimob'));

    // Then upload proof
    $proof = UploadedFile::fake()->image('proof.jpg');
    $uploadResponse = $this->post('/payment/baridimob/upload', ['proof' => $proof]);
    $uploadResponse->assertRedirect();
    $this->assertDatabaseHas('orders', ['guest_name' => 'Jane Doe', 'payment_method' => 'baridimob']);
    $order = \App\Models\Order::where('guest_name', 'Jane Doe')->first();
    $this->assertNotNull($order->payment_proof);
    Storage::disk('local')->assertExists($order->payment_proof);
});

test('Stripe redirects to checkout session - mocked', function () {
    Http::fake([
        'api.stripe.com/v1/checkout/sessions' => Http::response([
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.com/pay/cs_test_123',
        ], 200),
    ]);

    $product = Product::factory()->create(['price' => 50]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->post('/checkout', [
        'full_name'      => 'Stripe User',
        'email'          => 'stripe@example.com',
        'phone'          => '111222333',
        'address'        => '789 Pine St',
        'city'           => 'Constantine',
        'region'         => 'Constantine',
        'payment_method' => 'stripe',
    ]);

    $response->assertRedirect();
    $this->assertNotNull(Session::get('stripe_session_id'));
});
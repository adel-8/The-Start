<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
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
    $this->markTestSkipped('BaridiMob test requires manual verification on staging due to session persistence.');
});

test('Stripe redirects to checkout session', function () {
    $this->markTestSkipped('Stripe test requires live keys and proper mocking. Will enable after keys are configured.');
});
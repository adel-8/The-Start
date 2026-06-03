<?php

use App\Models\Address;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Roles are seeded once in TestCase
});

test('validates required fields for checkout', function () {
    $product = Product::factory()->create();
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->post('/checkout', []);
    $response->assertSessionHasErrors(['full_name', 'phone', 'address', 'city', 'payment_method']);
});

test('reduces product stock after successful checkout', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 3]);

    $this->post('/checkout', [
        'full_name'      => 'Stock Tester',
        'email'          => 'test@example.com',
        'phone'          => '123456789',
        'address'        => '123 St',
        'city'           => 'Algiers',
        'region'         => 'Algiers',
        'payment_method' => 'cash_on_delivery',
    ]);

    $this->assertEquals(2, $product->fresh()->stock);
});

test('prevents checkout when cart is empty', function () {
    $response = $this->post('/checkout', ['payment_method' => 'cash_on_delivery']);
    // The controller redirects back with validation errors (not a session flash)
    $response->assertSessionHasErrors(['full_name', 'phone', 'address', 'city']);
    $this->assertNull(session('last_order_number'));
});

test('prevents checkout when product is out of stock', function () {
    $product = Product::factory()->create(['stock' => 0]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->post('/checkout', [
        'full_name'      => 'Out Of Stock',
        'email'          => 'oos@example.com',
        'phone'          => '123456',
        'address'        => '123 St',
        'city'           => 'Algiers',
        'region'         => 'Algiers',
        'payment_method' => 'cash_on_delivery',
    ]);

    $response->assertSessionHas('error');
    $this->assertDatabaseMissing('orders', ['guest_name' => 'Out Of Stock']);
});

test('creates a new address for guest checkout', function () {
    $product = Product::factory()->create();
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $this->post('/checkout', [
        'full_name'      => 'New Address Guest',
        'email'          => 'guest@example.com',
        'phone'          => '5551234',
        'address'        => 'Brand New Street',
        'city'           => 'Blida',
        'region'         => 'Blida',
        'payment_method' => 'cash_on_delivery',
    ]);

    $this->assertDatabaseHas('addresses', ['address_line1' => 'Brand New Street', 'city' => 'Blida']);
});

test('reuses existing address for logged-in user (no duplicate)', function () {
    $user = User::factory()->create();
    $existingAddress = Address::factory()->create([
        'user_id' => $user->id,
        'address_line1' => 'Same Street',
        'city' => 'Algiers',
        'state' => 'Algiers',
    ]);
    $product = Product::factory()->create();

    $this->actingAs($user);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    // Submit with the exact same address details
    $this->post('/checkout', [
        'full_name'      => $user->name,
        'email'          => $user->email,
        'phone'          => '123456',
        'address'        => 'Same Street',
        'city'           => 'Algiers',
        'region'         => 'Algiers',
        'payment_method' => 'cash_on_delivery',
    ]);

    // No new address should be created
    $this->assertEquals(1, Address::where('address_line1', 'Same Street')->count());
});

test('guest checkout creates order with correct guest fields', function () {
    $product = Product::factory()->create();
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $this->post('/checkout', [
        'full_name'      => 'Guest User',
        'email'          => 'guest@example.com',
        'phone'          => '0987654321',
        'address'        => 'Guest Street',
        'city'           => 'Constantine',
        'region'         => 'Constantine',
        'payment_method' => 'cash_on_delivery',
    ]);

    $this->assertDatabaseHas('orders', [
        'guest_name' => 'Guest User',
        'guest_email' => 'guest@example.com',
        'guest_phone' => '0987654321',
        'user_id' => null,
    ]);
});
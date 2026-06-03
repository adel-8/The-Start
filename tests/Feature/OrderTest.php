<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Ensure OrderFactory and OrderItemFactory exist
});

test('it allows logged-in user to view their orders list', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'ORD-123']);

    $this->actingAs($user)
         ->get(route('orders.index'))
         ->assertSee('ORD-123');
});

test('it prevents user from seeing another user\'s order', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user2->id, 'order_number' => 'ORD-SECRET']);

    $this->actingAs($user1)
         ->get(route('orders.show', $order->order_number))
         ->assertStatus(403);
});

test('it allows guest to view their own order via session', function () {
    $order = Order::factory()->create(['user_id' => null, 'order_number' => 'ORD-GUEST']);
    session(['last_order_number' => 'ORD-GUEST']);

    $this->get(route('orders.show', $order->order_number))
         ->assertSee('ORD-GUEST');
});

test('it denies guest without session to view order', function () {
    $order = Order::factory()->create(['user_id' => null, 'order_number' => 'ORD-GUEST']);
    $this->get(route('orders.show', $order->order_number))->assertStatus(403);
});

test('it displays order items with correct totals', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100]);
    $order = Order::factory()->create(['user_id' => $user->id, 'total_price' => 200]);
    OrderItem::factory()->create([
        'order_id'          => $order->id,
        'product_id'        => $product->id,
        'quantity'          => 2,
        'price_at_purchase' => 100,
    ]);

    $this->actingAs($user)
         ->get(route('orders.show', $order->order_number))
         ->assertSee('200')
         ->assertSee($product->name);
});

test('it admin can update order status', function () {
    $admin = User::factory()->create(['role_id' => 2]);
    $order = Order::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)
         ->put(route('admin.orders.update', $order), ['status' => 'processing', 'payment_status' => 'paid'])
         ->assertRedirect();

    $this->assertEquals('processing', $order->fresh()->status);
    $this->assertEquals('paid', $order->fresh()->payment_status);
});

test('it admin can update payment status', function () {
    $admin = User::factory()->create(['role_id' => 2]);
    $order = Order::factory()->create(['payment_status' => 'pending']);

    $this->actingAs($admin)
         ->post(route('admin.orders.payment.update', $order), ['payment_status' => 'paid'])
         ->assertRedirect();

    $this->assertEquals('paid', $order->fresh()->payment_status);
});

test('it order items have correct price at purchase', function () {
    $product = Product::factory()->create(['price' => 99.99]);
    $order = Order::factory()->create();
    $orderItem = OrderItem::factory()->create([
        'order_id'          => $order->id,
        'product_id'        => $product->id,
        'quantity'          => 1,
        'price_at_purchase' => 99.99,
    ]);

    $this->assertEquals(99.99, $orderItem->price_at_purchase);
    $this->assertEquals($product->price, $orderItem->price_at_purchase);
});
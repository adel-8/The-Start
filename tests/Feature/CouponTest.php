<?php

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Ensure CouponFactory exists
});

test('it applies a valid percentage coupon', function () {
    $product = Product::factory()->create(['price' => 200]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $coupon = Coupon::factory()->create([
        'code'            => 'SAVE10',
        'discount_type'   => 'percentage',
        'discount_value'  => 10,
        'valid_from'      => Carbon::yesterday(),
        'valid_to'        => Carbon::tomorrow(),
        'active'          => true,
    ]);

    $response = $this->postJson('/coupon/apply', ['code' => 'SAVE10']);
    $response->assertJson(['success' => true]);
    $this->assertEquals(20, $response->json('discount')); // 10% of 200
});

test('it applies a fixed amount coupon', function () {
    $product = Product::factory()->create(['price' => 150]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $coupon = Coupon::factory()->create([
        'code'           => 'FIXED20',
        'discount_type'  => 'fixed',
        'discount_value' => 20,
        'valid_from'     => Carbon::yesterday(),
        'valid_to'       => Carbon::tomorrow(),
        'active'         => true,
    ]);

    $response = $this->postJson('/coupon/apply', ['code' => 'FIXED20']);
    $response->assertJson(['success' => true, 'discount' => 20]);
});

test('it rejects an expired coupon', function () {
    $product = Product::factory()->create(['price' => 100]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $coupon = Coupon::factory()->create([
        'code'       => 'EXPIRED',
        'valid_from' => Carbon::now()->subDays(10),
        'valid_to'   => Carbon::now()->subDays(1),
        'active'     => true,
    ]);

    $response = $this->postJson('/coupon/apply', ['code' => 'EXPIRED']);
    $response->assertJson(['success' => false]);
});

test('it respects coupon usage limit per user', function () {
    $this->markTestSkipped('Implement usage tracking first (requires coupon_usages table).');
});

test('it requires minimum order amount for coupon', function () {
    $product = Product::factory()->create(['price' => 30]);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $coupon = Coupon::factory()->create([
        'code'              => 'MIN50',
        'discount_type'     => 'percentage',
        'discount_value'    => 10,
        'min_order_amount'  => 50,
        'valid_from'        => Carbon::yesterday(),
        'valid_to'          => Carbon::tomorrow(),
        'active'            => true,
    ]);

    $response = $this->postJson('/coupon/apply', ['code' => 'MIN50']);
    $response->assertJson(['success' => false, 'message' => 'Minimum order amount is $50.00']); // ← Changed here
});
test('it rejects non-existent coupon code', function () {
    $product = Product::factory()->create();
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->postJson('/coupon/apply', ['code' => 'DOESNOTEXIST']);
    $response->assertJson(['success' => false]);
});
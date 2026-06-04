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
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100]);
    
    // Create coupon with limit 1 per user
    $coupon = Coupon::factory()->create([
        'code'                  => 'LIMIT1',
        'discount_type'         => 'fixed',
        'discount_value'        => 20,
        'usage_limit_per_user'  => 1,
        'valid_from'            => Carbon::yesterday(),
        'valid_to'              => Carbon::tomorrow(),
        'active'                => true,
    ]);

    // First order – should apply coupon
    $this->actingAs($user);
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
    
    $response1 = $this->postJson('/coupon/apply', ['code' => 'LIMIT1']);
    $response1->assertJson(['success' => true]);
    
    // Complete checkout to record usage
    $this->post('/checkout', [
        'full_name'      => $user->name,
        'email'          => $user->email,
        'phone'          => '123456789',
        'address'        => '123 Main St',
        'city'           => 'Algiers',
        'region'         => 'Algiers',
        'payment_method' => 'cash_on_delivery',
        'coupon_code'    => 'LIMIT1',
    ])->assertRedirect();

    // Clear cart for second attempt
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
    
    // Second attempt – should fail because limit reached
    $response2 = $this->postJson('/coupon/apply', ['code' => 'LIMIT1']);
    $response2->assertJson(['success' => false, 'message' => 'You have already reached the usage limit for this coupon.']);
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
<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Review;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it allows authenticated user to submit a review', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
         ->post(route('product.review.store', $product), [
             'rating'  => 5,
             'comment' => 'Excellent watch!',
         ])
         ->assertRedirect()
         ->assertSessionHas('success');

    $this->assertDatabaseHas('reviews', [
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'rating'     => 5,
        'approved'   => false,
    ]);
});

test('it prevents duplicate review from same user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'rating'     => 5,
        'comment'    => 'First review',
    ]);

    $this->actingAs($user)
         ->post(route('product.review.store', $product), [
             'rating'  => 4,
             'comment' => 'Another one',
         ])
         ->assertSessionHas('error', 'You have already reviewed this product.');
});

test('it requires authentication to write a review', function () {
    $product = Product::factory()->create();
    $this->post(route('product.review.store', $product), ['rating' => 5])
         ->assertRedirect('/signin');
});

test('it validates rating range', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)
         ->post(route('product.review.store', $product), ['rating' => 6, 'comment' => 'Too high'])
         ->assertSessionHasErrors('rating');
});

test('it admin can approve a review', function () {
    // Create admin (owner role)
    $admin = User::factory()->create(['role_id' => 1]);

    // Create a product and a regular user for the review
    $product = Product::factory()->create();
    $user = User::factory()->create();

    // Create a review manually
    $review = Review::create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'rating'     => 5,
        'comment'    => 'Pending review',
        'approved'   => false,
    ]);

    $this->actingAs($admin)
         ->post(route('admin.reviews.approve', $review))
         ->assertRedirect();

    // Direct database assertion - most reliable
    $this->assertDatabaseHas('reviews', [
        'id'       => $review->id,
        'approved' => 1,
    ]);
});

test('it admin can delete a review', function () {
    $admin = User::factory()->create(['role_id' => 2]);
    $review = Review::factory()->create();

    $this->actingAs($admin)
         ->delete(route('admin.reviews.destroy', $review))
         ->assertRedirect();

    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

test('it displays approved reviews on product page', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    $approvedReview = Review::factory()->create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'approved'   => true,
        'comment'    => 'Approved comment',
    ]);
    $pendingReview = Review::factory()->create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'approved'   => false,
        'comment'    => 'Pending comment',
    ]);

    $response = $this->get(route('product.show', $product->slug));
    $response->assertSee('Approved comment');
    $response->assertDontSee('Pending comment');
});

test('it calculates average rating correctly', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    Review::factory()->create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'rating'     => 5,
        'approved'   => true,
    ]);
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'rating'     => 3,
        'approved'   => true,
    ]);
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id'    => $user->id,
        'rating'     => 1,
        'approved'   => false,
    ]);

    $this->assertEquals(4.0, $product->averageRating());
});
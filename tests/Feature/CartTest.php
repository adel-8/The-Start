<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_item_to_cart()
    {
        $product = Product::factory()->create();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('cart');
        $cart = session('cart');
        $this->assertArrayHasKey($product->id, $cart);
        $this->assertEquals(1, $cart[$product->id]['quantity']);
    }

   public function test_logged_in_user_can_add_item_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(302);                        // changed from 200 to 302

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_cart_update_quantity()
    {
        $product = Product::factory()->create();
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->post('/cart/update', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertSessionHas('cart');

        $cart = session('cart');
        $this->assertEquals(3, $cart[$product->id]['quantity']);
    }

    public function test_cart_remove_item()
{
    $product = Product::factory()->create();
    $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

    $this->delete("/cart/remove/{$product->id}");

    $this->assertEquals([], session('cart'));
}
}
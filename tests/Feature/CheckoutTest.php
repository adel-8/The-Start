<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_checkout()
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post('/checkout', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'address' => '123 Main St',
            'city' => 'Algiers',
            'region' => 'Algiers',
            'postal_code' => '16000',
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['guest_name' => 'John Doe']);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 1]);
        $this->assertDatabaseMissing('products', ['id' => $product->id, 'stock' => 10]); // stock reduced
    }

    public function test_logged_in_user_can_checkout_with_saved_address()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'address_line1' => '456 Elm St',
            'city' => 'Oran',
        ]);
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($user)
            ->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->post('/checkout', [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '123456789',
            'address' => 'dummy',  // will be ignored because address_id is used
            'city' => 'dummy',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
    }
}
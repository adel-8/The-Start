<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['role_id' => 2]); // admin role
        $this->actingAs($admin)->get('/admin')->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin()
    {
        $user = User::factory()->create(['role_id' => 3]); // regular user
        $this->actingAs($user)->get('/admin')->assertStatus(403);
    }

    public function test_guest_cannot_access_admin()
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_can_create_product()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 99.99,
            'buy_price' => 50.00,
            'status' => 'active',
        ])->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}
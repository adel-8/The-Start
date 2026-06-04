<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Skip GD tests if not installed
    if (! extension_loaded('gd') && method_exists($this, 'markTestSkipped')) {
        // We'll skip the product creation test that requires image upload
    }
});

test('it allows admin to access admin dashboard', function () {
    $admin = User::factory()->create(['role_id' => 2]);
    $this->actingAs($admin)->get('/admin')->assertStatus(200);
});

test('it denies regular user from accessing admin dashboard', function () {
    $user = User::factory()->create(['role_id' => 3]);
    $this->actingAs($user)->get('/admin')->assertStatus(403);
});

test('it denies guest from accessing admin', function () {
    // Your app redirects guests to /signin (not /login)
    $this->get('/admin')->assertRedirect('/signin');
});

test('it allows admin to create a product', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension required for image upload.');
    }
    $admin = User::factory()->create(['role_id' => 2]);
    $response = $this->actingAs($admin)->post('/admin/products', [
        'name'      => 'Admin Product',
        'slug'      => 'admin-product',
        'buy_price' => 50,
        'price'     => 100,
        'status'    => 'active',
        'image'     => UploadedFile::fake()->image('product.jpg'),
    ]);
    $response->assertRedirect('/admin/products');
    $this->assertDatabaseHas('products', ['name' => 'Admin Product']);
});

test('it prevents regular user from creating a product', function () {
    $user = User::factory()->create(['role_id' => 3]);
    $this->actingAs($user)
         ->post('/admin/products', ['name' => 'Unauthorized', 'price' => 10])
         ->assertStatus(403);
});

test('it allows admin to edit any user', function () {
    $admin = User::factory()->create(['role_id' => 1]);
    $user = User::factory()->create();

    // Make sure the admin has permission; check your routes/middleware.
    // Some admin routes may require owner role. Adjust role_id if needed (1 = owner).
    $this->actingAs($admin)
         ->put("/admin/users/{$user->id}", ['name' => 'Updated Name', 'email' => $user->email, 'role_id' => 3])
         ->assertRedirect();

    $this->assertEquals('Updated Name', $user->fresh()->name);
});

test('it prevents regular user from editing another user', function () {
    $user1 = User::factory()->create(['role_id' => 3]);
    $user2 = User::factory()->create(['role_id' => 3]);

    $this->actingAs($user1)
         ->get("/admin/users/{$user2->id}/edit")
         ->assertStatus(403);
});

test('it only owner can access analytics page', function () {
    $owner = User::factory()->create(['role_id' => 1]); // owner role
    $admin = User::factory()->create(['role_id' => 2]);

    $this->actingAs($owner)->get('/admin/analytics')->assertStatus(200);
    $this->actingAs($admin)->get('/admin/analytics')->assertStatus(403);
});

test('it owner can view users list', function () {
    $owner = User::factory()->create(['role_id' => 1]);
    $this->actingAs($owner)->get('/admin/users')->assertStatus(200);
});

test('it admin cannot view users list', function () {
    $admin = User::factory()->create(['role_id' => 2]);
    $this->actingAs($admin)->get('/admin/users')->assertStatus(403);
});
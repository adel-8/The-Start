<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\ResetPassword;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Roles seeded once in TestCase
});

test('user can register', function () {
    $response = $this->post('/signup', [
        'firstName' => 'John',
        'lastName'  => 'Doe',
        'email'     => 'john@example.com',
        'password'  => 'password',
        'password_confirmation' => 'password',
        'terms'     => 1,
    ]);

    $response->assertRedirect('/');
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    $this->assertAuthenticated();
});

test('registration requires password confirmation', function () {
    $response = $this->post('/signup', [
        'firstName' => 'John',
        'lastName'  => 'Doe',
        'email'     => 'john@example.com',
        'password'  => 'password',
        'password_confirmation' => 'different',
        'terms'     => 1,
    ]);

    $response->assertSessionHasErrors('password');
});

test('user can login', function () {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/signin', [
        'email'    => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('login fails with wrong credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('correct')]);

    $this->post('/signin', [
        'email'    => $user->email,
        'password' => 'wrong',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('user can logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');
    $response->assertRedirect('/signin');
    $this->assertGuest();
});

test('password reset link can be sent', function () {
    $user = User::factory()->create();

    $response = $this->post('/forgot-password', ['email' => $user->email]);
    $response->assertSessionHas('status', __('passwords.sent'));
    
    // Optional: check that a reset token was created
    $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
});

test('password can be reset', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertRedirect('/signin');
    $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
});

test('login is throttled after 5 attempts', function () {
    $user = User::factory()->create(['email' => 'throttle@test.com', 'password' => bcrypt('correct')]);

    for ($i = 0; $i < 5; $i++) {
        $this->post('/signin', ['email' => 'throttle@test.com', 'password' => 'wrong'])
             ->assertSessionHasErrors('email');
    }

    // 6th attempt should be throttled (status 429)
    $this->post('/signin', ['email' => 'throttle@test.com', 'password' => 'wrong'])
         ->assertStatus(429);
});
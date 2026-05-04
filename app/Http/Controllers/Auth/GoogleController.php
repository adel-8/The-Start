<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        // In GoogleController@redirectToGoogle
        Log::info('Google redirect URI: ' . config('services.google.redirect'));
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('signin')->withErrors('Google login failed. Please try again.');
        }

        // Find user by email
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Create a new user with a random password
            $user = User::create([
                'name'              => $googleUser->getName(),
                'username'          => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'password'          => bcrypt(Str::random(16)),
                'role_id'           => 3, // default customer role
                'email_verified_at' => now(), // Google accounts are considered verified
            ]);
        }

        Auth::login($user, true);

        // Redirect to the intended page (or home)
        return redirect()->intended('/');
    }
}
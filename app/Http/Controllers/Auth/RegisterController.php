<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showSignupForm()
    {
        return view('signup');
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string|max:50',
            'lastName'  => 'required|string|max:50',
            'email'     => 'required|email|unique:users,email',
            'terms'     => 'accepted',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        $username = $request->firstName . ' ' . $request->lastName;

        $user = User::create([
            'name'     => $username,
            'username' => $username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => 3, // default customer role
        ]);

        // Send email verification without logging in immediately
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('success', 'Please check your email to verify your account before signing in.');
    }
}
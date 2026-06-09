<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        

        // After creating the user
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            Log::error('Signup: Failed to send verification email to ' . $user->email . ': ' . $e->getMessage());
            // Optionally flash a warning, but continue
            session()->flash('warning', 'Account created, but the verification email could not be sent. Please contact support or try resending later.');
        }

        // Log the user in
        Auth::login($user);

        // Redirect to intended page or home
        return redirect()->intended(route('home'));
    }
}
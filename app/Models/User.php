<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'profile_image',
        'age',
        'phone',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship: user has many addresses
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Relationship: user has many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    public function sendEmailVerificationNotification()
    {
        try {
            parent::sendEmailVerificationNotification();
        } catch (\Exception $e) {
            Log::error('Mail error: Could not send verification to ' . $this->email . ': ' . $e->getMessage());
            // Do not re-throw – prevent 500 error
        }
    }
}
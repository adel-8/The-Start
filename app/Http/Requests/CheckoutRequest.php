<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'regex:/^(\+213|0)(5|6|7)\d{8}$/'], // Algerian phone example
            'address' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash_on_delivery', // extend as needed
            'coupon_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'save_address' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => 'Please enter a valid Algerian phone number (e.g., 0555123456 or +213555123456).',
        ];
    }
}
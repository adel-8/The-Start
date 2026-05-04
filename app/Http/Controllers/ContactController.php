<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        // Fetch all contact-related settings at once
        $settings = Setting::pluck('setting_value', 'setting_key');

        $contact = [
            'phone'   => $settings['contact_phone'] ?? '+213 123 45 67 89',
            'email'   => $settings['contact_email'] ?? 'support@thestart.com',
            'email2'  => $settings['contact_email2'] ?? 'info@thestart.com',
            'address' => $settings['contact_address'] ?? '123 Algiers Street, Algiers, Algeria 16000',
            'map_url' => $settings['contact_map_url'] ?? 'https://www.openstreetmap.org/export/embed.html?bbox=2.9%2C36.7%2C3.2%2C36.9&layer=mapnik&marker=36.753768%2C3.058756',
            'faq'     => $settings['contact_faq'] ?? null,
        ];

        // Decode FAQ JSON or use default
        $faq = $contact['faq'] ? json_decode($contact['faq'], true) : null;
        if (!$faq) {
            $faq = [
                ['question' => 'How long does delivery take?', 'answer' => 'Delivery typically takes 2-5 business days depending on your location in Algeria.'],
                ['question' => 'Do you offer Cash on Delivery?', 'answer' => 'Yes! COD is our primary payment method for the Algerian market.'],
                ['question' => 'Can I return a product?', 'answer' => 'Yes, we offer a 14-day return policy for unused items in original packaging.'],
                ['question' => 'How can I track my order?', 'answer' => 'Log in to your account and visit the Orders section to track your order status.'],
            ];
        }

        return view('contact', compact('contact', 'faq'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create($request->only('name', 'email', 'phone', 'subject', 'message'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Your message has been sent!']);
        }

        return back()->with('success', 'Your message has been sent!');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function about()
    {
        // Fetch all about settings
        $settings = Setting::pluck('setting_value', 'setting_key');

        // Helper function to decode JSON fields
        $decodeJson = function($key, $default = []) use ($settings) {
            return isset($settings[$key]) ? json_decode($settings[$key], true) : $default;
        };

        // Fetch team members from database
        $teamMembers = TeamMember::where('active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($member) {
                return [
                    'name' => $member->name,
                    'role' => $member->role,
                    'bio' => $member->bio,
                    'image' => $member->image_url,
                ];
            })->toArray();

        // Prepare data for view
        $about = [
            'hero' => [
                'title' => $settings['about_hero_title'] ?? 'The Start',
                'tagline' => $settings['about_hero_tagline'] ?? 'Your Trusted E-Commerce Partner in Algeria',
                'description' => $settings['about_hero_description'] ?? 'We\'re building a simpler, more trustworthy way to shop online — focused on what matters most: simplicity, trust, and your complete satisfaction.',
            ],
            'mission' => [
                'title' => $settings['about_mission_title'] ?? 'Our Mission',
                'text' => $settings['about_mission_text'] ?? 'To provide a seamless, secure, and trustworthy e-commerce experience for every Algerian shopper, prioritizing Cash on Delivery, fast browsing, and genuine customer care.',
            ],
            'vision' => [
                'title' => $settings['about_vision_title'] ?? 'Our Vision',
                'text' => $settings['about_vision_text'] ?? 'To become Algeria\'s most trusted e-commerce platform, known for simplicity, reliability, and a deep understanding of local needs — empowering local businesses and shoppers alike.',
            ],
            'story' => [
                'title' => $settings['about_story_title'] ?? 'Our Story',
                'subtitle' => $settings['about_story_subtitle'] ?? 'Born from a simple idea: shopping should be easy and trustworthy',
                'text' => $settings['about_story_text'] ?? '<p>The Start was founded with a clear vision — to solve the real challenges faced by Algerian online shoppers. We understood that trust is the foundation of any successful e-commerce experience. That\'s why we built our platform around Cash on Delivery (COD), making it simple and secure for everyone.</p><p>From our humble beginnings, we\'ve grown into a platform that prioritizes fast browsing, clear communication, and a seamless user experience. Every feature we build, from our intuitive cart system to our transparent order tracking, is designed with one goal in mind: making your shopping journey effortless and enjoyable.</p><p>Today, The Start stands as a symbol of reliability in the Algerian market — a place where you can shop with confidence, knowing that your satisfaction is our top priority.</p>',
            ],
            'values' => $decodeJson('about_values', [
                ['title' => 'Simplicity First', 'text' => 'We believe shopping should be straightforward. Clean interfaces, clear flows, and intuitive design make every interaction effortless.', 'icon' => 'fas fa-sun'],
                ['title' => 'Trust-Driven UX', 'text' => 'From Cash on Delivery to transparent order status updates, we build every feature to earn and keep your trust.', 'icon' => 'fas fa-handshake'],
                ['title' => 'Security by Default', 'text' => 'Your data and privacy matter. We implement Laravel\'s best practices to ensure every transaction is secure and protected.', 'icon' => 'fas fa-lock'],
                ['title' => 'Modular Growth', 'text' => 'Built for the future, our platform is designed to grow with your needs — easily extendable and ready for new features.', 'icon' => 'fas fa-cubes'],
            ]),
            'features' => $decodeJson('about_features', [
                ['title' => 'Cash on Delivery', 'text' => 'Pay only when you receive your order. Simple, secure, and trusted across Algeria.', 'icon' => 'fas fa-money-bill-wave'],
                ['title' => 'Fast Browsing', 'text' => 'Lightning-fast product search and filtering to help you find what you need in seconds.', 'icon' => 'fas fa-search'],
                ['title' => 'Order Tracking', 'text' => 'Real-time updates on your order status — from confirmation to delivery at your doorstep.', 'icon' => 'fas fa-truck'],
                ['title' => '24/7 Support', 'text' => 'Our dedicated team is always here to help with any questions or concerns.', 'icon' => 'fas fa-headset'],
                ['title' => 'Local Expertise', 'text' => 'Built specifically for the Algerian market, understanding your needs and preferences.', 'icon' => 'fas fa-map-marker-alt'],
                ['title' => 'Secure Platform', 'text' => 'Built with Laravel\'s best practices — your data is always protected and secure.', 'icon' => 'fas fa-shield-alt'],
            ]),
            'team' => $teamMembers, // use database data
            'cta' => [
                'title' => $settings['about_cta_title'] ?? 'Ready to Start Shopping?',
                'text' => $settings['about_cta_text'] ?? 'Join thousands of satisfied customers who trust The Start for their online shopping needs.',
                'button_text' => $settings['about_cta_button_text'] ?? 'Shop Now',
                'button_link' => $settings['about_cta_button_link'] ?? route('Shop'),
            ],
        ];

        return view('about', compact('about'));
    }
}
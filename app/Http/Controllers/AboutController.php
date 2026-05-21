<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function about()
    {
        $locale = app()->getLocale();
        $settings = Setting::pluck('setting_value', 'setting_key');

        // Helper to get bilingual setting
        $get = function($key, $default = '') use ($settings, $locale) {
            $bilingualKey = $key . '_' . $locale;
            if (isset($settings[$bilingualKey]) && !empty($settings[$bilingualKey])) {
                return $settings[$bilingualKey];
            }
            // Fallback to English
            $englishKey = $key . '_en';
            return $settings[$englishKey] ?? $default;
        };

        // Helper to decode JSON fields
        $decodeJson = function($key, $default = []) use ($settings) {
            return isset($settings[$key]) ? json_decode($settings[$key], true) : $default;
        };

        // Team members (not translatable via settings, use database)
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

        // Prepare bilingual data for view
        $about = [
            'hero' => [
                'title'       => $get('about_hero_title', 'The Start'),
                'tagline'     => $get('about_hero_tagline', 'Your Trusted E-Commerce Partner in Algeria'),
                'description' => $get('about_hero_description', 'We\'re building a simpler, more trustworthy way to shop online.'),
            ],
            'mission' => [
                'title' => $get('about_mission_title', 'Our Mission'),
                'text'  => $get('about_mission_text', 'To provide a seamless, secure, and trustworthy e-commerce experience.'),
            ],
            'vision' => [
                'title' => $get('about_vision_title', 'Our Vision'),
                'text'  => $get('about_vision_text', 'To become Algeria\'s most trusted e-commerce platform.'),
            ],
            'story' => [
                'title'    => $get('about_story_title', 'Our Story'),
                'subtitle' => $get('about_story_subtitle', 'Born from a simple idea: shopping should be easy and trustworthy'),
                'text'     => $get('about_story_text', '<p>The Start was founded with a clear vision...</p>'),
            ],
            'values'   => $decodeJson('about_values', [
                ['title' => 'Simplicity First', 'text' => 'Clean interfaces and intuitive design.', 'icon' => 'fas fa-sun'],
                ['title' => 'Trust-Driven UX', 'text' => 'Cash on Delivery and transparent tracking.', 'icon' => 'fas fa-handshake'],
                ['title' => 'Security by Default', 'text' => 'Your data is always protected.', 'icon' => 'fas fa-lock'],
                ['title' => 'Modular Growth', 'text' => 'Built for future features.', 'icon' => 'fas fa-cubes'],
            ]),
            'features' => $decodeJson('about_features', [
                ['title' => 'Cash on Delivery', 'text' => 'Pay when you receive your order.', 'icon' => 'fas fa-money-bill-wave'],
                ['title' => 'Fast Browsing', 'text' => 'Find products in seconds.', 'icon' => 'fas fa-search'],
                ['title' => 'Order Tracking', 'text' => 'Real-time updates.', 'icon' => 'fas fa-truck'],
                ['title' => '24/7 Support', 'text' => 'Always here to help.', 'icon' => 'fas fa-headset'],
                ['title' => 'Local Expertise', 'text' => 'Built for Algeria.', 'icon' => 'fas fa-map-marker-alt'],
                ['title' => 'Secure Platform', 'text' => 'Laravel best practices.', 'icon' => 'fas fa-shield-alt'],
            ]),
            'team' => $teamMembers,
            'cta' => [
                'title'       => $get('about_cta_title', 'Ready to Start Shopping?'),
                'text'        => $get('about_cta_text', 'Join thousands of satisfied customers.'),
                'button_text' => $get('about_cta_button_text', 'Shop Now'),
                'button_link' => $settings['about_cta_button_link'] ?? route('Shop'),
            ],
        ];

        // Also pass raw settings for any additional headings used in view (like team heading)
        // But the view can also use the $get helper if needed.
        // We'll pass the helper or just pass settings.
        return view('about', compact('about', 'settings'));
    }
}
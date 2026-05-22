<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function terms()
    {
        $locale = app()->getLocale();
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();

        $content = $settings['terms_of_service_' . $locale] ?? $settings['terms_of_service_en'] ?? 'Terms of service content not available.';
        $title = $locale === 'ar' ? 'شروط الخدمة' : 'Terms of Service';

        return view('pages.terms', compact('content', 'title', 'settings'));
    }

    public function privacy()
    {
        $locale = app()->getLocale();
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();

        $content = $settings['privacy_policy_' . $locale] ?? $settings['privacy_policy_en'] ?? 'Privacy policy content not available.';
        $title = $locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy';

        return view('pages.privacy', compact('content', 'title', 'settings'));
    }

    public function returnPolicy()
    {
        $locale = app()->getLocale();
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();

        $content = $settings['return_policy_' . $locale] ?? $settings['return_policy_en'] ?? 'Return policy content not available.';
        $title = $locale === 'ar' ? 'سياسة الإرجاع' : 'Return Policy';

        return view('pages.return-policy', compact('content', 'title', 'settings'));
    }

    public function shippingPolicy()
    {
        $locale = app()->getLocale();
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray(); // FIXED: toArray() not to_array()

        $content = $settings['shipping_policy_' . $locale] ?? $settings['shipping_policy_en'] ?? 'Shipping policy content not available.';
        $title = $locale === 'ar' ? 'سياسة الشحن' : 'Shipping Policy';

        return view('pages.shipping-policy', compact('content', 'title', 'settings'));
    }
}
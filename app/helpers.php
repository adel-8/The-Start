<?php

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

if (!function_exists('cartCount')) {
    function cartCount()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->sum('quantity');
        }
        $cart = session('cart', []);
        return array_sum(array_column($cart, 'quantity'));
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol()
    {
        return app()->getLocale() === 'ar' ? 'دج' : 'DZD';
    }
}

if (!function_exists('resolve_localized_setting_value')) {
    function resolve_localized_setting_value($value, $locale = null)
    {
        $locale = $locale ?: app()->getLocale() ?: 'en';

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                if (isset($decoded[$locale]) && $decoded[$locale] !== null) {
                    return $decoded[$locale];
                }
                if (isset($decoded['en'])) {
                    return $decoded['en'];
                }
                if (isset($decoded['ar'])) {
                    return $decoded['ar'];
                }
            }
        }

        return $value;
    }
}

if (!function_exists('localized_setting')) {
    function localized_setting($key, array $settings, $default = null)
    {
        $locale = app()->getLocale() ?: 'en';
        $localeKey = $key . '_' . $locale;
        $fallbackKey = $locale === 'ar' ? $key . '_en' : $key . '_ar';

        if (array_key_exists($localeKey, $settings) && $settings[$localeKey] !== null && $settings[$localeKey] !== '') {
            return resolve_localized_setting_value($settings[$localeKey], $locale);
        }

        if (array_key_exists($fallbackKey, $settings) && $settings[$fallbackKey] !== null && $settings[$fallbackKey] !== '') {
            return resolve_localized_setting_value($settings[$fallbackKey], $locale);
        }

        if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '') {
            return resolve_localized_setting_value($settings[$key], $locale);
        }

        return $default;
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount, $decimals = 2)
    {
        return number_format($amount, $decimals) . ' ' . currency_symbol();
    }
}
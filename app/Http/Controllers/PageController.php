<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function terms()
    {
        $content = Setting::where('setting_key', 'terms_of_service')->value('setting_value');
        return view('pages.legal', [
            'title' => __('messages.terms_of_service'),
            'content' => $content ?? __('messages.terms_content_default')
        ]);
    }

    public function privacy()
    {
        $content = Setting::where('setting_key', 'privacy_policy')->value('setting_value');
        return view('pages.legal', [
            'title' => __('messages.privacy_policy'),
            'content' => $content ?? __('messages.privacy_content_default')
        ]);
    }
}
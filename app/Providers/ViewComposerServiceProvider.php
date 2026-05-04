<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Share $settings with all views
        View::composer('*', function ($view) {
            $settings = cache()->remember('site_settings', 3600, function () {
                return Setting::pluck('setting_value', 'setting_key')->toArray();
            });

            $resolvedSettings = $settings;
            foreach ($settings as $key => $value) {
                if (!str_ends_with($key, '_en') && !str_ends_with($key, '_ar')) {
                    $resolvedSettings[$key] = localized_setting($key, $settings, $value);
                }
            }
            foreach ($settings as $key => $value) {
                if (str_ends_with($key, '_en') || str_ends_with($key, '_ar')) {
                    $baseKey = substr($key, 0, -3);
                    if (!array_key_exists($baseKey, $resolvedSettings)) {
                        $resolvedSettings[$baseKey] = localized_setting($baseKey, $settings);
                    }
                }
            }

            $view->with('settings', $resolvedSettings);
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    public function register()
    {
        //
    }
}

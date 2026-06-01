<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->environment('production') && empty(config('app.key'))) {
            throw new \RuntimeException('Missing APP_KEY in production. Run php artisan key:generate.');
        }

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::auth(function ($request) {
                return env('TELESCOPE_ENABLED', false) && $this->app->environment('local');
            });
        }
    }
}

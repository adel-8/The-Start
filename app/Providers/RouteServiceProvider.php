<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        // Define named rate limiters with custom responses so we can return
        // friendlier messages for throttled form submissions.
        RateLimiter::for('signup', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $req, array $headers) {
                if ($req->expectsJson()) {
                    return response()->json(['message' => 'Too many signup attempts. Please wait a minute and try again.'], 429, $headers);
                }
                return redirect()->back()->withInput()->withErrors(['error' => 'Too many signup attempts. Please wait a minute and try again.']);
            });
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function (Request $req, array $headers) {
                if ($req->expectsJson()) {
                    return response()->json(['message' => 'Too many contact submissions. Please wait a minute and try again.'], 429, $headers);
                }
                return redirect()->back()->withInput()->withErrors(['error' => 'Too many contact submissions. Please wait a minute and try again.']);
            });
        });

        RateLimiter::for('forgot_password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function (Request $req, array $headers) {
                if ($req->expectsJson()) {
                    return response()->json(['message' => 'Too many password reset attempts. Please wait a minute and try again.'], 429, $headers);
                }
                return redirect()->back()->withInput()->withErrors(['error' => 'Too many password reset attempts. Please wait a minute and try again.']);
            });
        });

        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeadersMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Basic headers (already present)
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (disables unnecessary browser features)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Content Security Policy (adjust as needed for your assets)
        // For a basic store, this allows Stripe, Google Fonts, and your own assets
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' https://js.stripe.com https://checkout.stripe.com; "
             . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com; "
             . "img-src 'self' data: https:; "
             . "frame-src https://js.stripe.com https://checkout.stripe.com;";
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS (only on HTTPS, recommended for production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
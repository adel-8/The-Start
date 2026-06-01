<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers.
     */
    public function handle(Request $request, Closure $next)
    {
        // Generate a per-request nonce for inline scripts/styles
        try {
            $nonce = base64_encode(random_bytes(16));
        } catch (\Exception $e) {
            $nonce = bin2hex(random_bytes(8));
        }

        // Make the nonce available to all Blade views as $cspNonce
        // Use in templates: <script nonce="{{ $cspNonce }}"> or <style nonce="{{ $cspNonce }}">.
        view()->share('cspNonce', $nonce);

        $response = $next($request);

        // Content-Security-Policy: starter policy that allows self, listed CDNs,
        // and inline scripts/styles only when accompanied by the nonce.
        $csp = "default-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com https://js.stripe.com; ";
        $csp .= "script-src 'self' 'nonce-{$nonce}' https://cdnjs.cloudflare.com https://js.stripe.com; ";
        $csp .= "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com; ";
        $csp .= "font-src https://fonts.gstatic.com; ";
        $csp .= "img-src 'self' data:; ";
        $csp .= "connect-src 'self' https://js.stripe.com;";

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}

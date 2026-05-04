<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon; // Add this import

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            app()->setLocale($locale);
            setlocale(LC_TIME, $locale); // optional for strftime
            Carbon::setLocale($locale);
        }
        return $next($request);
    }
}
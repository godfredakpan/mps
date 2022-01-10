<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $locale = 'en';
        if (function_exists('mps_config')) {
            $locale = mps_config('language');
        }
        app()->setlocale(session('language', $locale));
        return $next($request);
    }
}

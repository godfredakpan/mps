<?php

namespace Modules\MPS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Impersonate
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('impersonate') && function_exists('mps_config') && mps_config('impersonation')) {
            Auth::onceUsingId($request->session()->get('impersonate'));
        }

        return $next($request);
    }
}

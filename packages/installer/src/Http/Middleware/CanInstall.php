<?php

namespace Tecdiary\Installer\Http\Middleware;

use Closure;

class CanInstall
{
    public function handle($request, Closure $next)
    {
        if (env('APP_INSTALLED', false) == true) {
            return redirect('/');
        }

        return $next($request);
    }
}

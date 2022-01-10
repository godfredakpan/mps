<?php

namespace Modules\MPS\Http\Middleware;

use Closure;

class RedirectIfNotAjaxRequest
{
    public function handle($request, Closure $next)
    {
        if (!request()->ajax() && request()->is('/mps/app/*')) {
            return redirect('/');
        }

        return $next($request);
    }
}

<?php

namespace Modules\MPS\Http\Middleware;

use Closure;

class CheckForCustomer
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if ($user && $user->customer_id) {
            $route = module_data('Shop', 'route');
            return redirect($route);
        }
        return $next($request);
    }
}

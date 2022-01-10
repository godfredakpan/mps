<?php

namespace Modules\MPS\Http\Middleware;

use Closure;

class CheckForLocationId
{
    public function handle($request, Closure $next)
    {
        $location_id = $request->session()->get('location_id', false);
        $location    = $location_id ? \Modules\MPS\Models\Location::whereId($location_id)->exists() : false;
        if (!$location_id || !$location) {
            abort(422, 'Please select location first!');
        }
        return $next($request);
    }
}

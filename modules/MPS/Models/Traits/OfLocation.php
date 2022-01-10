<?php

namespace Modules\MPS\Models\Traits;

trait OfLocation
{
    public static function scopeOfLocation($query)
    {
        return $query->where('location_id', session('location_id', null));
    }
}

<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Location;

trait BelongsToLocation
{
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

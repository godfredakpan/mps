<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Supplier;

trait BelongsToSupplier
{
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

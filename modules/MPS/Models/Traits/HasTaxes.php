<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Tax;

trait HasTaxes
{
    public function taxes()
    {
        return $this->morphToMany(Tax::class, 'taxable');
    }
}

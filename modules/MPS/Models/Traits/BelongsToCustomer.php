<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Customer;

trait BelongsToCustomer
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

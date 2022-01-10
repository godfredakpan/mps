<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Payment;

trait DirectPayments
{
    public function directPayments()
    {
        return $this->hasMany(Payment::class);
    }
}

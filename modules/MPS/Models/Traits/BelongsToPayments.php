<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Payment;

trait BelongsToPayments
{
    public function payments()
    {
        return $this->belongsToMany(Payment::class)->withPivot('amount');
    }
}

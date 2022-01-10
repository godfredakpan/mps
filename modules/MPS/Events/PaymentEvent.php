<?php

namespace Modules\MPS\Events;

use Modules\MPS\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentEvent
{
    use SerializesModels;

    public $payment;

    public $updating;

    public function __construct(Payment $payment, $updating = false)
    {
        $this->payment  = $payment;
        $this->updating = $updating;
    }
}

<?php

namespace Modules\MPS\Events;

use Modules\MPS\Models\Purchase;
use Illuminate\Queue\SerializesModels;

class PurchaseEvent
{
    use SerializesModels;

    public $method;

    public $original_purchase;

    public $purchase;

    public function __construct(Purchase $purchase, $method = 'created', $original_purchase = null)
    {
        $this->method            = $method;
        $this->purchase          = $purchase;
        $this->original_purchase = $original_purchase;
    }
}

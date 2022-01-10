<?php

namespace Modules\MPS\Events;

use Modules\MPS\Models\ReturnOrder;
use Illuminate\Queue\SerializesModels;

class ReturnOrderEvent
{
    use SerializesModels;

    public $method;

    public $original_return_order;

    public $return_order;

    public function __construct(ReturnOrder $return_order, $method = 'created', $original_return_order = null)
    {
        $this->method                = $method;
        $this->return_order          = $return_order;
        $this->original_return_order = $original_return_order;
    }
}

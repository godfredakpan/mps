<?php

namespace Modules\MPS\Events;

use Modules\MPS\Models\Sale;
use Illuminate\Queue\SerializesModels;

class SaleEvent
{
    use SerializesModels;

    public $method;

    public $original_sale;

    public $sale;

    public function __construct(Sale $sale, $method = 'created', $original_sale = null)
    {
        $this->method        = $method;
        $this->sale          = $sale;
        $this->original_sale = $original_sale;
    }
}

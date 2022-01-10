<?php

namespace Modules\MPS\Events;

use Illuminate\Queue\SerializesModels;
use Modules\MPS\Models\StockAdjustment;

class StockAdjustmentEvent
{
    use SerializesModels;

    public $method;

    public $original_adjustment;

    public $stock_adjustment;

    public function __construct(StockAdjustment $stock_adjustment, $method = 'created', $original_adjustment = null)
    {
        $this->method              = $method;
        $this->stock_adjustment    = $stock_adjustment;
        $this->original_adjustment = $original_adjustment;
    }
}

<?php

namespace Modules\MPS\Events;

use Modules\MPS\Models\StockTransfer;
use Illuminate\Queue\SerializesModels;

class StockTransferEvent
{
    use SerializesModels;

    public $method;

    public $original_transfer;

    public $stock_transfer;

    public function __construct(StockTransfer $stock_transfer, $method = 'created', $original_transfer = null)
    {
        $this->method            = $method;
        $this->stock_transfer    = $stock_transfer;
        $this->original_transfer = $original_transfer;
    }
}

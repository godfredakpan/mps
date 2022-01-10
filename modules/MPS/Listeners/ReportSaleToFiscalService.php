<?php

namespace Modules\MPS\Listeners;

use Modules\MPS\Events\SaleEvent;
use Illuminate\Support\Facades\Log;
use Modules\MPS\FiscalServices\SendSaleToFiscalService;

class ReportSaleToFiscalService
{
    public $delay = 60;

    public $tries = 3;

    public function failed(SaleEvent $event, $exception)
    {
        Log::error('ReportSaleToFiscalService failed!', ['Error' => $exception, 'sale' => $event->sale]);
    }

    public function handle(SaleEvent $event)
    {
        (new SendSaleToFiscalService($event->sale, $event->original_sale))->handle();
    }
}

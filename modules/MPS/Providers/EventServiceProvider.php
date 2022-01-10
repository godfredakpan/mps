<?php

namespace Modules\MPS\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'Illuminate\Auth\Events\Login' => [
            'Modules\MPS\Listeners\StartTimeClock',
            'Modules\MPS\Listeners\ForcePasswordChange',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'Modules\MPS\Listeners\EndTimeClock',
        ],
        'Modules\MPS\Events\RegisterRecordEvent' => [
            'Modules\MPS\Listeners\TimeClockWithRegister',
        ],
        'Modules\MPS\Events\PaymentEvent' => [
            'Modules\MPS\Listeners\SyncPaymentToSales',
            'Modules\MPS\Listeners\SyncPaymentToPurchases',
        ],
        'Modules\MPS\Events\StockTransferEvent' => [
            'Modules\MPS\Listeners\SyncItemLocationStock',
        ],
        'Modules\MPS\Events\StockAdjustmentEvent' => [
            'Modules\MPS\Listeners\AdjustmentEventListner',
        ],
        'Modules\MPS\Events\SaleEvent' => [
            'Modules\MPS\Listeners\SaleEventListener',
            'Modules\MPS\Listeners\ReportSaleToFiscalService',
            'Modules\MPS\Listeners\CalculateInventoryAccounting',
        ],
        'Modules\MPS\Events\PurchaseEvent' => [
            'Modules\MPS\Listeners\PurchaseEventListener',
            'Modules\MPS\Listeners\SetCostingForOverSoldItems',
        ],
        'Modules\MPS\Events\ReturnOrderEvent' => [
            'Modules\MPS\Listeners\ReturnOrderEventListener',
        ],
        'Modules\MPS\Events\AttachmentEvent' => [
            'Modules\MPS\Listeners\AttachmentEventListener',
        ],
    ];

    protected $subscribe = [
        // 'Modules\MPS\Listeners\PaymentEventSubscriber',
    ];

    public function boot()
    {
        parent::boot();
    }
}

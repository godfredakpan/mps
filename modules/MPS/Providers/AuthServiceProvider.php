<?php

namespace Modules\MPS\Providers;

use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Order;
use Modules\MPS\Models\Income;
use Modules\MPS\Models\Expense;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Models\Quotation;
use Modules\MPS\Models\ReturnOrder;
use Illuminate\Support\Facades\Gate;
use Modules\MPS\Models\AssetTransfer;
use Modules\MPS\Models\StockTransfer;
use Modules\MPS\Models\StockAdjustment;
use Modules\MPS\Policies\RecordOwnerUpdatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Customer::class        => RecordOwnerUpdatePolicy::class,
        Expense::class         => RecordOwnerUpdatePolicy::class,
        Income::class          => RecordOwnerUpdatePolicy::class,
        Order::class           => RecordOwnerUpdatePolicy::class,
        Purchase::class        => RecordOwnerUpdatePolicy::class,
        Sale::class            => RecordOwnerUpdatePolicy::class,
        Quotation::class       => RecordOwnerUpdatePolicy::class,
        Supplier::class        => RecordOwnerUpdatePolicy::class,
        StockAdjustment::class => RecordOwnerUpdatePolicy::class,
        StockTransfer::class   => RecordOwnerUpdatePolicy::class,
        AssetTransfer::class   => RecordOwnerUpdatePolicy::class,
        ReturnOrder::class     => RecordOwnerUpdatePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super')) {
                return true;
            }
        });
    }
}

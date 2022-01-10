<?php

namespace Modules\MPS\Listeners;

use Modules\MPS\Models\Sale;
use Illuminate\Support\Facades\Log;
use Modules\MPS\Events\PaymentEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncPaymentToSales implements ShouldQueue
{
    public function failed(PaymentEvent $event, $exception)
    {
        Log::error('SyncPaymentToSales failed!', ['payment' => $event->payment, 'Error' => $exception]);
    }

    public function handle(PaymentEvent $event)
    {
        if ($event->payment->payable instanceof \Modules\MPS\Models\Customer) {
            if ($event->payment->sale_id) {
                $sale = Sale::find($event->payment->sale_id);
                $sale->payments()->attach($event->payment->id, ['amount' => $event->payment->amount]);
                $paid_amount   = $sale->payments->isEmpty() ? 0 : $sale->payments->sum('amount');
                $paying_amount = $sale->grand_total - $paid_amount;
                if (!$sale->paid && $paying_amount <= $event->payment->amount) {
                    $sale->update(['paid' => 1]);
                }
            } else {
                if ($event->updating) {
                    $event->payment->sales->each(function ($sale) {
                        $sale->payments()->detach();
                        $sale->update(['paid' => null]);
                    });
                }
                $sales   = Sale::active()->unpaid()->ofCustomer($event->payment->payable_id)->orderBy('date')->get();
                $balance = $event->payment->amount;
                foreach ($sales as $sale) {
                    $paid_amount   = $sale->payments->isEmpty() ? 0 : $sale->payments->sum('amount');
                    $paying_amount = $sale->grand_total - $paid_amount;
                    if ($paying_amount <= $balance) {
                        $sale->payments()->attach($event->payment->id, ['amount' => $paying_amount]);
                        $sale->update(['paid' => 1]);
                        $balance -= $paying_amount;
                    } elseif ($paying_amount > $balance) {
                        $sale->payments()->attach($event->payment->id, ['amount' => $balance]);
                        break;
                    } else {
                        break;
                    }
                }
            }
        }
    }
}

<?php

namespace Modules\MPS\Listeners;

use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Modules\MPS\Events\PurchaseEvent;

class PurchaseEventListener
{
    public $purchase;

    public function failed(PurchaseEvent $event, $exception)
    {
        Log::error('PurchaseEventListener failed!', [
            'Error'    => $exception,
            'purchase' => $event->purchase,
        ]);
    }

    public function handle(PurchaseEvent $event)
    {
        $this->{$event->method}($event);
    }

    // private function addStockTrail(PurchaseItem $item, $quantity, $location, $type)
    // {
    //     $base_quantity = convert_to_base_quantity($quantity, $item->unit);
    //     $item->stockTrails()->create([
    //         'location_id'   => $location,
    //         'quantity'      => $quantity,
    //         'base_quantity' => $base_quantity,
    //         'item_id'       => $item->item_id,
    //         'unit_id'       => $item->unit_id,
    //         'type'          => 'purchase_item_' . $type,
    //     ]);
    // }

    private function completed($original_purchase, $type)
    {
        $ajt = $this->purchase->supplier->journal
            ->creditDollars($this->purchase->grand_total, 'purchase_' . $type)
            ->referencesObject($this->purchase);
        $this->purchase->disableLogging();
        $this->purchase->transaction_id = $ajt->id;
        $this->purchase->saveQuietly();

        // TODO: mark purchase paid check if supplier has balance
        if (!$original_purchase || $original_purchase->draft) {
            if (safe_email($this->purchase->supplier->email)) {
                $this->purchase->supplier->notify(new \Modules\MPS\Notifications\PurchaseCreated($this->purchase));
            }
        }

        // $this->purchase = $this->purchase->fresh();
        foreach ($this->purchase->items as $item) {
            $stock = $item->stock->first();
            if ($stock) {
                $total_cost = ($stock->avg_cost * $stock->quantity) + ($item->base_unit_cost * $item->balance);
                $total_qty  = $stock->quantity                      + $item->balance;
                $avg_cost   = formatDecimal($total_cost / $total_qty);
                $stock->update(['quantity' => $total_qty, 'avg_cost' => $avg_cost]);
            } else {
                $item->stock()->create(['quantity' => $item->balance, 'avg_cost' => $item->base_unit_cost, 'location_id' => $this->purchase->location_id]);
            }
        }
    }

    private function completedReset($original_purchase, $type)
    {
        $original_purchase->supplier->journal
            ->debitDollars($original_purchase->grand_total, 'purchase_' . $type)
            ->referencesObject($original_purchase);

        if (!empty($original_purchase->items)) {
            foreach ($original_purchase->items as $item) {
                $stock         = $item->stock->isNotEmpty() ? $item->stock->where('location_id', $original_purchase->location_id)->first() : null;
                $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                if ($stock) {
                    $balance_cost = ($stock->avg_cost * $stock->quantity) - ($item->base_unit_cost * $base_quantity);
                    $balance_qty  = $stock->quantity                      - $base_quantity;
                    $avg_cost     = $balance_qty ? $balance_cost / $balance_qty : $balance_cost;
                    $stock->update(['quantity' => $stock->quantity - $base_quantity, 'avg_cost' => $avg_cost]);
                } else {
                    $item->stock()->create(['quantity' => 0 - $base_quantity, 'avg_cost' => $item->base_unit_cost, 'location_id' => $original_purchase->location_id]);
                }
            }
        }
    }

    private function created(PurchaseEvent $event)
    {
        if (!$event->purchase->draft) {
            $this->purchase = Purchase::with(['items', 'supplier', 'items.stock'])->find($event->purchase->id);
            $this->completed(null, 'created');
        }
    }

    private function deleting(PurchaseEvent $event)
    {
        if (!$event->purchase->draft) {
            $this->purchase = Purchase::with(['items', 'supplier', 'items.stock'])->find($event->purchase->id);
            $this->completedReset($this->purchase, 'deleting');
        }
    }

    private function updated(PurchaseEvent $event)
    {
        if (!$event->original_purchase->draft) {
            $this->completedReset($event->original_purchase, 'editing');
        }
        if (!$event->purchase->draft) {
            $this->purchase = Purchase::with(['items', 'supplier', 'items.stock'])->find($event->purchase->id);
            $this->completed($event->original_purchase, 'updated');
        }
    }
}

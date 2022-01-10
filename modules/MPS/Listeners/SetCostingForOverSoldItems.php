<?php

namespace Modules\MPS\Listeners;

use Modules\MPS\Models\Costing;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Modules\MPS\Models\OverSelling;
use Modules\MPS\Events\PurchaseEvent;

class SetCostingForOverSoldItems
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

    private function completed($original_purchase, $type)
    {
        $items = $this->purchase->items->pluck('item_id');
        $oSold = OverSelling::whereIn('item_id', $items)->oldest()->get();
        if ($oSold->isNotEmpty()) {
            foreach ($oSold as $sold) {
                $sold_quantity  = $sold->quantity;
                $purchase_items = $this->purchase->items->where('item_id', $sold->item_id)->all();
                if (mps_config('inventory_accounting') == 'AVCO') {
                    $stock    = $sold->item->stock()->first();
                    $avg_cost = $stock->avg_cost;
                }
                foreach ($purchase_items as $pi) {
                    if ($sold_quantity > 0) {
                        if ($pi->balance < $sold_quantity) {
                            Costing::create([
                                'purchase_item_id' => $pi->id,
                                'sale_item_id'     => $sold->sale_item_id,
                                'item_id'          => $sold->item_id,
                                'sale_id'          => $sold->sale_id,
                                'purchase_id'      => $pi->purchase_id,
                                'variation_id'     => $sold->variation_id,
                                'quantity'         => $pi->balance,
                                'net_cost'         => $avg_cost ?? $pi->base_net_cost,
                                'cost'             => $avg_cost ?? $pi->base_unit_cost,
                                'price'            => $sold->price,
                                'net_price'        => $sold->net_price,
                            ]);
                            $pi->update(['balance' => 0]);
                            $sold_quantity -= $pi->balance;
                            $sold->update(['quantity' => $sold_quantity]);
                        } else {
                            Costing::create([
                                'purchase_item_id' => $pi->id,
                                'sale_item_id'     => $sold->sale_item_id,
                                'item_id'          => $sold->item_id,
                                'sale_id'          => $sold->sale_id,
                                'purchase_id'      => $pi->purchase_id,
                                'variation_id'     => $sold->variation_id,
                                'quantity'         => $sold_quantity,
                                'net_cost'         => $avg_cost ?? $pi->base_net_cost,
                                'cost'             => $avg_cost ?? $pi->base_unit_cost,
                                'price'            => $sold->price,
                                'net_price'        => $sold->net_price,
                            ]);
                            $pi->update(['balance' => ($pi->balance - $sold_quantity)]);
                            $sold_quantity = 0;
                            $sold->delete();
                            break;
                        }
                    }
                }
            }
        }
    }

    private function completedReset($original_purchase, $type)
    {
        if (!empty($original_purchase->items)) {
            $costings = Costing::where('purchase_id', $original_purchase->id)->get();
            foreach ($original_purchase->items as $item) {
                $purchase_costing = $costings->where('purchase_item_id', $item->id)->all();
                foreach ($purchase_costing as $costing) {
                    OverSelling::create([
                        'item_id'      => $costing->item_id,
                        'sale_id'      => $costing->sale_id,
                        'sale_item_id' => $costing->sale_item_id,
                        'variation_id' => $costing->variation_id,
                        'quantity'     => $costing->quantity,
                        'net_price'    => $costing->net_price,
                        'price'        => $costing->price,
                    ]);
                }
            }
        }
    }

    private function created(PurchaseEvent $event)
    {
        if (!$event->purchase->draft) {
            // $this->purchase = $event->purchase;
            $this->purchase = Purchase::with('items')->find($event->purchase->id);
            $this->completed(null, 'created');
        }
    }

    private function deleting(PurchaseEvent $event)
    {
        if (!$event->purchase->draft) {
            // $this->purchase = $event->purchase;
            $this->purchase = Purchase::with('items')->find($event->purchase->id);
            $this->completedReset($this->purchase, 'deleting');
        }
    }

    private function updated(PurchaseEvent $event)
    {
        if (!$event->original_purchase->draft) {
            $this->completedReset($event->original_purchase, 'editing');
        }
        if (!$event->purchase->draft) {
            // $this->purchase = $event->purchase;
            $this->purchase = Purchase::with('items')->find($event->purchase->id);
            $this->completed($event->original_purchase, 'updated');
        }
    }
}

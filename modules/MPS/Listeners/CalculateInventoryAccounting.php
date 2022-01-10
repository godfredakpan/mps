<?php

namespace Modules\MPS\Listeners;

use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Costing;
use Modules\MPS\Events\SaleEvent;
use Illuminate\Support\Facades\Log;
use Modules\MPS\Models\OverSelling;
use Modules\MPS\Models\PurchaseItem;

class CalculateInventoryAccounting
{
    public $method;

    public $sale;

    public function failed(SaleEvent $event, $exception)
    {
        Log::error('CalculateInventoryAccounting failed!', [
            'Error' => $exception,
            'sale'  => $event->sale,
        ]);
    }

    public function handle(SaleEvent $event)
    {
        $this->method = mps_config('inventory_accounting');
        $this->{$event->method}($event);
    }

    private function completed($original_sale, $type)
    {
        foreach ($this->sale->items as $item) {
            $purchasedItems = $this->getPurchasedItems($item->item_id);
            $net_price      = calculate_base_costing($item->net_price, $item->unit);
            $price          = calculate_base_costing($item->unit_price, $item->unit);
            $base_quantity  = convert_to_base_quantity($item->quantity, $item->unit);
            if ($this->method == 'AVCO') {
                $stock    = $item->item->stock()->first();
                $avg_cost = $stock->avg_cost;
            }
            foreach ($purchasedItems as $pi) {
                if ($base_quantity > 0) {
                    if ($pi->balance < $base_quantity) {
                        Costing::create([
                            'purchase_item_id' => $pi->id,
                            'sale_item_id'     => $item->id,
                            'item_id'          => $item->item_id,
                            'sale_id'          => $this->sale->id,
                            'purchase_id'      => $pi->purchase_id,
                            'variation_id'     => $item->variation_id,
                            'quantity'         => $pi->balance,
                            'net_cost'         => $avg_cost ?? $pi->base_net_cost,
                            'cost'             => $avg_cost ?? $pi->base_unit_cost,
                            'net_price'        => $net_price,
                            'price'            => $price,
                        ]);
                        $pi->update(['balance' => 0]);
                        $base_quantity = $base_quantity - $pi->balance;
                    } else {
                        Costing::create([
                            'purchase_item_id' => $pi->id,
                            'sale_item_id'     => $item->id,
                            'item_id'          => $item->item_id,
                            'sale_id'          => $this->sale->id,
                            'purchase_id'      => $pi->purchase_id,
                            'variation_id'     => $item->variation_id,
                            'quantity'         => $base_quantity,
                            'net_cost'         => $avg_cost ?? $pi->base_net_cost,
                            'cost'             => $avg_cost ?? $pi->base_unit_cost,
                            'net_price'        => $net_price,
                            'price'            => $price,
                        ]);
                        $pi->update(['balance' => ($pi->balance - $base_quantity)]);
                        $base_quantity = 0;
                        break;
                    }
                }
            }
            if ($base_quantity && $base_quantity > 0) {
                OverSelling::create([
                    'item_id'      => $item->item_id,
                    'sale_id'      => $this->sale->id,
                    'sale_item_id' => $item->id,
                    'variation_id' => $item->variation_id,
                    'quantity'     => $base_quantity,
                    'net_price'    => $net_price,
                    'price'        => $price,
                ]);
            }
        }
    }

    private function completedReset($original_sale, $type)
    {
        $costings = Costing::where('sale_id', $original_sale->id)->get();
        foreach ($costings as $costing) {
            PurchaseItem::where('purchase_id', $costing->purchase_id)->where('id', $costing->purchase_item_id)->update(['balance' => $costing->quantity]);
            $costing->delete();
        }
    }

    private function created(SaleEvent $event)
    {
        if (!$event->sale->draft) {
            // $this->sale = $event->sale;
            $this->sale = Sale::with('items')->find($event->sale->id);
            $this->completed(null, 'created');
        }
    }

    private function deleting(SaleEvent $event)
    {
        if (!$event->sale->draft) {
            // $this->sale = $event->sale;
            $this->sale = Sale::with('items')->find($event->sale->id);
            $this->completedReset($this->sale, 'deleting');
        }
    }

    private function getPurchasedItems($item_id)
    {
        $items = PurchaseItem::query()
            ->where('item_id', $item_id)->where('balance', '>', 0);
        if ($this->method == 'FIFO') {
            $items->oldest();
        } elseif ($this->method == 'LIFO') {
            $items->latest();
        } elseif ($this->method == 'ITEX') {
            $items->orderBy('expiry_date', 'asc');
        }
        return $items->get();
    }

    private function updated(SaleEvent $event)
    {
        if ($event->original_sale && !$event->original_sale->draft) {
            $this->completedReset($event->original_sale, 'editing');
        }
        if (!$event->sale->draft) {
            // $this->sale = $event->sale;
            $this->sale = Sale::with('items')->find($event->sale->id);
            $this->completed($event->original_sale, 'updated');
        }
    }
}

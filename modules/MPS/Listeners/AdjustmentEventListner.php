<?php

namespace Modules\MPS\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MPS\Models\StockAdjustment;
use Modules\MPS\Models\StockAdjustmentItem;
use Modules\MPS\Events\StockAdjustmentEvent;

class AdjustmentEventListner
{
    public $stock_adjustment;

    public function __call($name, $arguments)
    {
        Log::info('AdjustmentEventListner method called!', [
            'method'    => $name,
            'arguments' => implode(', ', $arguments),
        ]);
    }

    public function created(StockAdjustmentEvent $event)
    {
        $this->stock_adjustment = StockAdjustment::with(['items.stock'])->find($event->stock_adjustment->id);
        $this->{$event->stock_adjustment->type == 'addition' ? 'addition' : 'damage'}('created');
    }

    public function deleting(StockAdjustmentEvent $event)
    {
        $this->stock_adjustment = StockAdjustment::with(['items.stock'])->find($event->stock_adjustment->id);
        $this->{$event->stock_adjustment->type == 'addition' ? 'additionReset' : 'damageReset'}($this->stock_adjustment, 'deleting');
    }

    public function failed(StockAdjustmentEvent $event, $exception)
    {
        Log::error('StockAdjustmentEvent failed!', [
            'Error'            => $exception,
            'stock_adjustment' => $event->stock_adjustment,
        ]);
    }

    public function handle(StockAdjustmentEvent $event)
    {
        $this->{$event->method}($event);
    }

    public function updated(StockAdjustmentEvent $event)
    {
        $this->{$event->stock_adjustment->type == 'addition' ? 'additionReset' : 'damageReset'}($event->original_adjustment, 'editing');
        $this->stock_adjustment = StockAdjustment::with(['items.stock'])->find($event->stock_adjustment->id);
        $this->{$event->stock_adjustment->type == 'addition' ? 'addition' : 'damage'}('updated');
    }

    private function addition($type)
    {
        // $this->stock_adjustment = $this->stock_adjustment->fresh();
        if (!$this->stock_adjustment->draft) {
            foreach ($this->stock_adjustment->items as $item) {
                $stock = $item->stock->first();
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity + $item->quantity]);
                } else {
                    $item->stock()->create(['quantity' => $item->quantity, 'location_id' => $this->stock_adjustment->location_id]);
                }
            }
        }
    }

    private function additionReset($original_adjustment, $type)
    {
        if (!$original_adjustment->draft) {
            foreach ($original_adjustment->items as $item) {
                $stock = $item->stock->first();
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity - $item->quantity]);
                } else {
                    $item->stock()->create(['quantity' => 0 - $item->quantity, 'location_id' => $original_adjustment->location_id]);
                }
            }
        }
    }

    private function addStockTrail(StockAdjustmentItem $item, $quantity, $location, $type)
    {
        $item->stockTrails()->create([
            'location_id' => $location,
            'quantity'    => $quantity,
            'item_id'     => $item->item_id,
            'unit_id'     => $item->unit_id,
            'type'        => 'stock_adjustment_item_' . $type,
        ]);
    }

    private function damage($type)
    {
        if (!$this->stock_adjustment->draft) {
            foreach ($this->stock_adjustment->items as $item) {
                $stock = $item->stock->first();
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity - $item->quantity]);
                } else {
                    $item->stock()->create(['quantity' => 0 - $item->quantity, 'location_id' => $this->stock_adjustment->location_id]);
                }
            }
        }
    }

    private function damageReset($original_adjustment, $type)
    {
        if (!$original_adjustment->draft) {
            foreach ($original_adjustment->items as $item) {
                $stock = $item->stock->first();
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity + $item->quantity]);
                } else {
                    $item->stock()->create(['quantity' => $item->quantity, 'location_id' => $original_adjustment->location_id]);
                }
            }
        }
    }
}

<?php

namespace Modules\MPS\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MPS\Models\StockTransfer;
use Modules\MPS\Models\StockTransferItem;
use Modules\MPS\Events\StockTransferEvent;

class SyncItemLocationStock
{
    public $stock_transfer;

    public function __call($name, $arguments)
    {
        Log::info('SyncItemLocationStock method called!', [
            'method'    => $name,
            'arguments' => implode(', ', $arguments),
        ]);
    }

    public function created(StockTransferEvent $event)
    {
        $this->stock_transfer = StockTransfer::with(['items.stock'])->find($event->stock_transfer->id);
        $this->{$event->stock_transfer->status}('created');
    }

    public function deleting(StockTransferEvent $event)
    {
        $this->stock_transfer = StockTransfer::with(['items.stock'])->find($event->stock_transfer->id);
        $this->{$this->stock_transfer->status . 'Reset'}($this->stock_transfer, 'deleting');
    }

    public function failed(StockTransferEvent $event, $exception)
    {
        Log::error('StockTransferEvent failed!', [
            'Error'          => $exception,
            'stock_transfer' => $event->stock_transfer,
        ]);
    }

    public function handle(StockTransferEvent $event)
    {
        $this->{$event->method}($event);
    }

    public function updated(StockTransferEvent $event)
    {
        $this->{$event->original_transfer->status . 'Reset'}($event->original_transfer, 'editing');
        $this->stock_transfer = StockTransfer::with(['items.stock'])->find($event->stock_transfer->id);
        $this->{$event->stock_transfer->status}('updated');
    }

    private function addStockTrail(StockTransferItem $item, $quantity, $location, $type)
    {
        $item->stockTrails()->create([
            'location_id' => $location,
            'quantity'    => $quantity,
            'item_id'     => $item->item_id,
            'unit_id'     => $item->unit_id,
            'type'        => 'stock_transfer_item_' . $type,
        ]);
    }

    private function transferred($type)
    {
        $this->stock_transfer = $this->stock_transfer->fresh();
        foreach ($this->stock_transfer->items as $item) {
            $from = $item->stock->where('location_id', $this->stock_transfer->from)->first();
            if ($from) {
                $from_array = $from->toArray();
                $from->update(['quantity' => $from_array['quantity'] - $item->quantity]);
            // $this->addStockTrail($item, (0 - $item->quantity), $this->stock_transfer->from, 'created');
            } else {
                $item->stock()->create(['quantity' => 0 - $item->quantity, 'location_id' => $this->stock_transfer->from]);
            }
            $to = $item->stock->where('location_id', $this->stock_transfer->to)->first();
            if ($to) {
                $to_array = $to->toArray();
                $to->update(['quantity' => $to_array['quantity'] + $item->quantity]);
            // $this->addStockTrail($item, $item->quantity, $this->stock_transfer->to, 'created');
            } else {
                $item->stock()->create(['quantity' => $item->quantity, 'location_id' => $this->stock_transfer->to]);
            }
        }
    }

    private function transferredReset($original_transfer, $type)
    {
        foreach ($original_transfer->items as $item) {
            $from = $item->stock->where('location_id', $original_transfer->from)->first();
            if ($from) {
                $from_array = $from->toArray();
                $from->update(['quantity' => $from_array['quantity'] + $item->quantity]);
            // $this->addStockTrail($item, $item->quantity, $original_transfer->from, $type);
            } else {
                $item->stock()->create(['quantity' => $item->quantity, 'location_id' => $this->stock_transfer->from]);
            }

            $to = $item->stock->where('location_id', $original_transfer->to)->first();
            if ($to) {
                $to_array = $to->toArray();
                $to->update(['quantity' => $to_array['quantity'] - $item->quantity]);
            // $this->addStockTrail($item, (0 - $item->quantity), $original_transfer->to, $type);
            } else {
                $item->stock()->create(['quantity' => 0 - $item->quantity, 'location_id' => $this->stock_transfer->to]);
            }
        }
    }

    private function transferring($type)
    {
        foreach ($this->stock_transfer->items as $item) {
            $from = $item->stock->where('location_id', $this->stock_transfer->from)->first();
            $from->update(['quantity' => $from->quantity - $item->quantity]);
            // $this->addStockTrail($item, (0 - $item->quantity), $this->stock_transfer->from, 'created');
        }
    }

    private function transferringReset($original_transfer, $type)
    {
        foreach ($original_transfer->items as $item) {
            $from       = $item->stock->where('location_id', $original_transfer->from)->first();
            $from_array = $from->toArray();
            $from->update(['quantity' => $from_array['quantity'] + $item->quantity]);
            // $this->addStockTrail($item, $item->quantity, $original_transfer->from, $type);
        }
    }
}

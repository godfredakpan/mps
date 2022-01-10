<?php

namespace Modules\MPS\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MPS\Models\ReturnOrder;
use Modules\MPS\Models\ReturnOrderItem;
use Modules\MPS\Events\ReturnOrderEvent;
use Modules\MPS\Notifications\ReturnOrderCreated;

class ReturnOrderEventListener
{
    public $return_order;

    public function failed(ReturnOrderEvent $event, $exception)
    {
        Log::error('ReturnOrderEventListener failed!', [
            'Error'        => $exception,
            'return_order' => $event->return_order,
        ]);
    }

    public function handle(ReturnOrderEvent $event)
    {
        $this->{$event->method}($event);
    }

    private function addStockTrail(ReturnOrderItem $item, $quantity, $location, $type)
    {
        $base_quantity = convert_to_base_quantity($quantity, $item->unit);
        $item->stockTrails()->create([
            'location_id'   => $location,
            'quantity'      => $quantity,
            'base_quantity' => $base_quantity,
            'item_id'       => $item->item_id,
            'unit_id'       => $item->unit_id,
            'type'          => 'return_order_item_' . $type,
        ]);
    }

    private function completeReturnOrder($original_return_order, $type)
    {
        if ($this->return_order->type == 'sale' && $this->return_order->customer) {
            $ajt = $this->return_order->customer->journal
                ->debitDollars($this->return_order->grand_total, 'return_order_' . $type)
                ->referencesObject($this->return_order);
        } elseif ($this->return_order->type == 'purchase' && $this->return_order->supplier) {
            $ajt = $this->return_order->supplier->journal
                ->creditDollars($this->return_order->grand_total, 'return_order_' . $type)
                ->referencesObject($this->return_order);
        }
        if ($ajt) {
            $this->return_order->disableLogging();
            $temp = $this->return_order->getEventDispatcher();
            $this->return_order->unsetEventDispatcher();
            $this->return_order->update(['transaction_id' => $ajt->id]);
            $this->return_order->setEventDispatcher($temp);
        }
        // TODO: mark return_order paid check if customer has balance
        if (!$original_return_order || $original_return_order->draft) {
            if ($this->return_order->customer_id) {
                if (safe_email($this->return_order->customer->email) && !default_customer($this->return_order->customer_id)) {
                    $this->return_order->customer->notify(new ReturnOrderCreated($this->return_order));
                }
            }
            if ($this->return_order->supplier_id) {
                if (safe_email($this->return_order->supplier->email)) {
                    $this->return_order->supplier->notify(new ReturnOrderCreated($this->return_order));
                }
            }
        }

        // $this->return_order = $this->return_order->fresh();
        foreach ($this->return_order->items as $item) {
            $stock         = $item->stock->first();
            $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
            if ($stock) {
                $stock->update([
                    'quantity' => $this->return_order->type == 'sale' ? $stock->quantity + $base_quantity : $stock->quantity - $base_quantity,
                ]);
            } else {
                $item->stock()->create(['quantity' => $this->return_order->type == 'sale' ? $base_quantity : 0 - $base_quantity, 'location_id' => $this->return_order->location_id]);
            }
        }

        if ($this->return_order->type == 'sale' && $this->return_order->customer) {
            $loyalty = mps_config('loyalty');
            if ($loyalty) {
                if ($loyalty->customer && $loyalty->customer->spent && $loyalty->customer->points) {
                    $points = floor(($this->return_order->grand_total / $loyalty->customer->spent) * $loyalty->customer->points);
                    if ($points != 0) {
                        $this->return_order->customer->update(['points' => $this->return_order->customer->points - $points]);
                    }
                }
                if ($loyalty->staff && $loyalty->staff->spent && $loyalty->staff->points) {
                    $points = floor(($this->return_order->grand_total / $loyalty->staff->spent) * $loyalty->staff->points);
                    if ($points != 0) {
                        $this->return_order->user->update(['points' => $this->return_order->user->points - $points]);
                    }
                }
            }
        }
    }

    private function created(ReturnOrderEvent $event)
    {
        $this->return_order = ReturnOrder::with(['items', 'customer', 'supplier', 'items.stock'])->find($event->return_order->id);
        $this->completeReturnOrder(null, 'created');
    }

    private function deleting(ReturnOrderEvent $event)
    {
        $this->return_order = ReturnOrder::with(['items', 'customer', 'supplier', 'items.stock'])->find($event->return_order->id);
        $this->resetReturnOrder($this->return_order, 'deleting');
    }

    private function resetReturnOrder($original_return_order, $type)
    {
        if ($original_return_order->type == 'sale' && $original_return_order->customer) {
            $original_return_order->customer->journal
                ->creditDollars($original_return_order->grand_total, 'return_order_' . $type)
                ->referencesObject($original_return_order);
        } elseif ($original_return_order->type == 'purchase' && $original_return_order->supplier) {
            $original_return_order->supplier->journal
                ->debitDollars($original_return_order->grand_total, 'return_order_' . $type)
                ->referencesObject($original_return_order);
        }

        if ($original_return_order->items) {
            foreach ($original_return_order->items as $item) {
                $stock         = $item->stock->isNotEmpty() ? $item->stock->where('location_id', $original_return_order->location_id)->first() : null;
                $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                if ($stock) {
                    $stock->update([
                        'quantity' => $original_return_order->type == 'sale' ? $stock->quantity - $base_quantity : $stock->quantity + $base_quantity,
                    ]);
                } else {
                    $item->stock()->create(['quantity' => $original_return_order->type == 'sale' ? 0 - $base_quantity : $base_quantity, 'location_id' => $original_return_order->location_id]);
                }
            }
        }

        if ($original_return_order->type == 'sale' && $original_return_order->customer) {
            $loyalty = mps_config('loyalty');
            if ($loyalty) {
                if ($loyalty->customer && $loyalty->customer->spent && $loyalty->customer->points) {
                    $points = floor(($original_return_order->grand_total / $loyalty->customer->spent) * $loyalty->customer->points);
                    if ($points != 0) {
                        $original_return_order->customer->update(['points' => $original_return_order->customer->points + $points]);
                    }
                }
                if ($loyalty->staff && $loyalty->staff->spent && $loyalty->staff->points) {
                    $points = floor(($original_return_order->grand_total / $loyalty->staff->spent) * $loyalty->staff->points);
                    if ($points != 0) {
                        $original_return_order->user->update(['points' => $original_return_order->user->points + $points]);
                    }
                }
            }
        }
    }

    private function updated(ReturnOrderEvent $event)
    {
        $this->resetReturnOrder($event->original_return_order, 'editing');
        $this->return_order = ReturnOrder::with(['items', 'customer', 'supplier', 'items.stock'])->find($event->return_order->id);
        $this->completeReturnOrder($event->original_return_order, 'updated');
    }
}

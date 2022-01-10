<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Stock;

class StockObserver
{
    public function created(Stock $stock)
    {
        $item = $stock->item;
        $item->stockTrails()->create([
            'item_id'     => $item->item_id,
            'unit_id'     => $item->unit_id,
            'memo'        => 'StockObserver',
            'quantity'    => $stock->quantity,
            'location_id' => $stock->location_id,
            'type'        => 'item_stock_created',
        ]);
    }

    public function deleting(Stock $stock)
    {
        $stock->item->stockTrails()->delete();
    }

    public function updating(Stock $stock)
    {
        if (!$stock->wasRecentlyCreated && $stock->isDirty('quantity')) {
            $item = $stock->item;
            $item->stockTrails()->create([
                'item_id'     => $item->item_id,
                'unit_id'     => $item->unit_id,
                'memo'        => 'StockObserver',
                'location_id' => $stock->location_id,
                'type'        => 'item_stock_editing',
                'quantity'    => (0 - $stock->getOriginal('quantity')),
            ]);
            $item->stockTrails()->create([
                'item_id'     => $item->item_id,
                'unit_id'     => $item->unit_id,
                'memo'        => 'StockObserver',
                'quantity'    => $stock->quantity,
                'location_id' => $stock->location_id,
                'type'        => 'item_stock_updated',
            ]);
        }
    }
}

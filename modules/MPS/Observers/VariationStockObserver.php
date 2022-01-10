<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\VariationStock;

class VariationStockObserver
{
    public function created(VariationStock $stock)
    {
        $item = $stock->variation->item;
        $item->stockTrails()->create([
            'item_id'      => $item->item_id,
            'unit_id'      => $item->unit_id,
            'quantity'     => $stock->quantity,
            'location_id'  => $stock->location_id,
            'variation_id' => $stock->variation_id,
            'type'         => 'item_variation_stock_created',
        ]);
    }

    public function deleting(VariationStock $stock)
    {
        $stock->item->stockTrails()->delete();
    }

    public function updating(VariationStock $stock)
    {
        if (!$stock->wasRecentlyCreated && ($stock->getOriginal('quantity') != $stock->quantity)) {
            $item = $stock->variation->item;
            if ($item->item_id) {
                if ($stock->getOriginal('quantity')) {
                    $item->stockTrails()->create([
                        'item_id'      => $item->item_id,
                        'unit_id'      => $item->unit_id,
                        'location_id'  => $stock->location_id,
                        'variation_id' => $stock->variation_id,
                        'type'         => 'item_variation_stock_editing',
                        'quantity'     => (0 - $stock->getOriginal('quantity')),
                    ]);
                }
                if ($stock->quantity) {
                    $item->stockTrails()->create([
                        'item_id'      => $item->item_id,
                        'unit_id'      => $item->unit_id,
                        'quantity'     => $stock->quantity,
                        'location_id'  => $stock->location_id,
                        'variation_id' => $stock->variation_id,
                        'type'         => 'item_variation_stock_updated',
                    ]);
                }
            }
        }
    }
}

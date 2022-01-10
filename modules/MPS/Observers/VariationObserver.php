<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Variation;

class VariationObserver
{
    public function created(Variation $variation)
    {
        $item = $variation->item;
        $item->stockTrails()->create([
            'item_id'      => $item->item_id,
            'unit_id'      => $item->unit_id,
            'variation_id' => $variation->id,
            'quantity'     => $variation->quantity,
            'type'         => 'item_variation_created',
        ]);
    }

    public function deleting(Variation $variation)
    {
        $variation->item->stockTrails()->delete();
    }

    public function updating(Variation $variation)
    {
        if (!$variation->wasRecentlyCreated && ($variation->getOriginal('quantity') != $variation->quantity)) {
            $item = $variation->item;
            $item->stockTrails()->create([
                'item_id'      => $item->item_id,
                'unit_id'      => $item->unit_id,
                'variation_id' => $variation->id,
                'type'         => 'item_variation_editing',
                'quantity'     => (0 - $variation->getOriginal('quantity')),
            ]);
            $item->stockTrails()->create([
                'item_id'      => $item->item_id,
                'unit_id'      => $item->unit_id,
                'variation_id' => $variation->id,
                'quantity'     => $variation->quantity,
                'type'         => 'item_variation_updated',
            ]);
        }
    }
}

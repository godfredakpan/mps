<?php

namespace Modules\MPS\Services;

use Modules\MPS\Models\Portion;
use Modules\MPS\Models\Variation;
use Modules\MPS\Models\ModifierOption;

class DeliveryDataService
{
    public function __invoke($v)
    {
        $items = collect($v['items'])->transform(function ($item) {
            if (!empty($item['selected']['serials'])) {
                $item['serials'] = $item['selected']['serials'];
            }
            if (!empty($item['selected']['modifiers'])) {
                $options = [];
                $mIds = collect($item['selected']['modifiers'])->pluck('selected');
                $selectedModifierOption = ModifierOption::whereIn('id', $mIds)->get();
                foreach ($item['selected']['modifiers'] as $m) {
                    $option = $selectedModifierOption->find($m['selected']);
                    $option->price = $option->item->price;
                    $options[$option->id] = (new DeliveryItemService)($m, $item, $option);
                    $options[$option->id]['modifier_id'] = $m['id'];
                }
                $item['modifierOptions'] = $options;
            }
            if (!empty($item['selected']['variations']) || !empty($item['selected']['portions'])) {
                if (!empty($item['selected']['variations'])) {
                    $variations = [];
                    $vIds = collect($item['selected']['variations'])->pluck('id');
                    $selectedVariations = Variation::whereIn('id', $vIds)->with('locationStock')->get();
                    foreach ($item['selected']['variations'] as $v) {
                        $variation = $selectedVariations->find($v['id']);
                        $variations[$variation->id] = (new DeliveryItemService)($v, $item, $variation, true);
                    }
                    $item['variations'] = $variations;
                }
                if (!empty($item['selected']['portions'])) {
                    $portions = [];
                    $pIds = collect($item['selected']['portions'])->pluck('id');
                    $selectedPortions = Portion::whereIn('id', $pIds)->get();
                    foreach ($item['selected']['portions'] as $p) {
                        $portion = $selectedPortions->find($p['id']);
                        $portions[$portion->id] = (new DeliveryItemService)($p, $item, $portion);
                    }
                    $item['portions'] = $portions;
                }
            }

            unset($item['selected']);
            return $item;
        });

        $v['items'] = $items;
        return $v;
    }
}

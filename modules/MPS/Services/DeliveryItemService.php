<?php

namespace Modules\MPS\Services;

class DeliveryItemService
{
    public function __invoke($v, $item, $r = null, $location = false)
    {
        if ($r && $location) {
            $location = $r->locationStock->first();
        }

        $data = [
            'quantity' => $v['quantity'],
            'unit_id'  => $v['unit_id'] ?? null,
        ];
        if (!empty($v['choosables'])) {
            foreach ($v['choosables'] as $choosable) {
                $data['choosables'][] = [
                    'id'      => $choosable['id'],
                    'item_id' => $choosable['selected'],
                ];
            }
        }
        return $data;
    }
}

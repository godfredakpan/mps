<?php

namespace Modules\MPS\Services;

use Modules\MPS\Models\Promotion;
use Modules\MPS\Actions\TaxAction;
use Modules\MPS\Actions\DiscountAction;

class OrderItemService
{
    public function __invoke($v, $item, $r = false, $location = false, $purchase = false)
    {
        if ($r && $location) {
            $location = $r->locationStock->first();
        }
        $field = $purchase ? 'cost' : 'price';
        if (is_array($v) && isset($v[$field])) {
            $vfield = $v[$field];
        } else {
            $vfield = $location && $location->$field ? $location->$field : ($r && $r->$field ? $r->$field : $item[$field]);
        }
        $v['discount_amount'] = (new DiscountAction())->calculate($item['discount'], $vfield);

        if (!empty($item['promotions'])) {
            $promotions = Promotion::whereIn('id', $item['promotions'])->get();
            foreach ($promotions as $promotion) {
                if ($promotion['type'] != 'BXGY') {
                    $v['discount_amount'] += (new DiscountAction())->calculate($promotion['discount'], ($vfield - $v['discount_amount']));
                }
            }
        }

        $v['net_' . $field] = $vfield - $v['discount_amount'];
        $v['taxes']         = (new TaxAction())->calculate($item['taxes'] ?? false, $v['net_' . $field], $v['quantity'], !empty($v['tax_included']));
        // if (!empty($v['tax_included']) && $v['taxes']) {
        //     $v['net_' . $field] -= $v['taxes']->sum('amount');
        // }
        $v['tax_amount']            = $v['taxes'] ? formatDecimal($v['taxes']->sum('amount')) : 0;
        $v['total_tax_amount']      = formatDecimal($v['tax_amount'] * $v['quantity']);
        $v['total_discount_amount'] = formatDecimal($v['discount_amount'] * $v['quantity']);
        $v['unit_' . $field]        = formatDecimal($v['net_' . $field] + $v['tax_amount']);

        $data = [
            $field                  => $vfield,
            'quantity'              => $v['quantity'],
            'unit_id'               => $v['unit_id'] ?? null,
            'net_' . $field         => $v['net_' . $field],
            'unit_' . $field        => $v['unit_' . $field],
            'tax_amount'            => $v['tax_amount'],
            'discount_amount'       => $v['discount_amount'],
            'total_tax_amount'      => $v['total_tax_amount'],
            'total_discount_amount' => $v['total_discount_amount'],
            'total'                 => formatDecimal($v['quantity'] * $v['unit_' . $field]),
        ];
        if (!empty($v['choosables'])) {
            foreach ($v['choosables'] as $choosable) {
                $data['choosables'][] = [
                    'id'           => $choosable['id'],
                    'item_id'      => $choosable['selected'],
                    'variation_id' => $choosable['variation_id'],
                ];
            }
        }
        if (!empty($v['essentials'])) {
            foreach ($v['essentials'] as $essential) {
                $essential_data = [
                    'id'           => $essential['id'],
                    'item_id'      => $essential['item_id'],
                    'variation_id' => $essential['variation_id'],
                ];
                $e = $r ? $r->essentials->firstWhere('id', $essential['id']) : false;
                if ($e) {
                    $essential_data['quantity']   = $e->quantity;
                    $essential_data['portion_id'] = $e->portion_id;
                }
                $data['essentials'][] = $essential_data;
            }
        }
        if (!empty($v['portion_items'])) {
            foreach ($v['portion_items'] as $portion_item) {
                $portion_item_data = [
                    'id'           => $portion_item['id'],
                    'item_id'      => $portion_item['item_id'],
                    'variation_id' => $portion_item['variation_id'] ?? null,
                ];
                $e = $r && $r->portionItems ? $r->portionItems->firstWhere('id', $portion_item['id']) : false;
                if ($e) {
                    $portion_item_data['quantity']   = $e->quantity;
                    $portion_item_data['portion_id'] = $e->portion_id;
                }
                $data['portion_items'][] = $portion_item_data;
            }
        }

        return $data;
    }
}

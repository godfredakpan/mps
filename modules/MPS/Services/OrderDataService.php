<?php

namespace Modules\MPS\Services;

use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Portion;
use Modules\MPS\Models\Modifier;
use Modules\MPS\Models\Promotion;
use Modules\MPS\Models\Variation;
use Modules\MPS\Actions\TaxAction;
use Modules\MPS\Models\ModifierOption;
use Modules\MPS\Actions\DiscountAction;

class OrderDataService
{
    public function __invoke($v, $purchase = false, $adjustment = false)
    {
        $items = collect($v['items'])->transform(function ($item, $index) use ($purchase, $adjustment) {
            if ($purchase || $adjustment) {
                $cost = 0;
                $subtotal = 0;
                $tax_amount = $total_tax_amount = 0;
                $discount_amount = $total_discount_amount = 0;
                if ($adjustment && !empty($item['selected']['serials'])) {
                    $item['serials'] = $item['selected']['serials'];
                } elseif ($purchase && !empty($item['selected']['serials'])) {
                    $item['serials'] = [];
                    $serials = $item['selected']['serials'];
                    foreach ($serials as $serial) {
                        if (isset($serial['till']) && !empty($serial['till'])) {
                            $total_serials = $serial['till'] - $serial['number'];
                            for ($i = 0; $i < $total_serials; $i++) {
                                $item['serials'][] = $serial['number'] + $i;
                            }
                        } elseif (isset($serial['number']) && !empty($serial['number'])) {
                            $item['serials'][] = $serial['number'];
                        }
                    }
                }
                if (!empty($item['selected']['variations'])) {
                    $variations = [];
                    $vIds = collect($item['selected']['variations'])->pluck('id');
                    $selectedVariations = Variation::whereIn('id', $vIds)->with('locationStock')->get();
                    foreach ($item['selected']['variations'] as $v) {
                        // $variation = Variation::find($v['id']);
                        $variation = $selectedVariations->find($v['id']);
                        $variations[$variation->id] = (new OrderItemService())($v, $item, $variation, true, true);
                        $cost += $variations[$variation->id]['cost'];
                        // $cost += $variations[$variation->id]['net_cost'];
                        $tax_amount += $variations[$variation->id]['tax_amount'];
                        $discount_amount += $variations[$variation->id]['discount_amount'];
                        $total_tax_amount += $variations[$variation->id]['total_tax_amount'];
                        $total_discount_amount += $variations[$variation->id]['total_discount_amount'];
                        $subtotal += $variations[$variation->id]['total'];
                    }
                    $item['cost'] = $cost;
                    $item['variations'] = $variations;
                    $item['tax_amount'] = $tax_amount;
                    $item['discount_amount'] = $discount_amount;
                    $item['total_tax_amount'] = $total_tax_amount;
                    $item['total_discount_amount'] = $total_discount_amount;
                    $item['net_cost'] = $item['cost'] - $item['discount_amount'];
                    $item['unit_cost'] = formatDecimal($item['net_cost'] + $item['tax_amount']);
                    $item['subtotal'] = formatDecimal($subtotal, 2);
                } else {
                    $item['discount_amount'] = (new DiscountAction())->calculate($item['discount'], $item['cost']);
                    $item['total_discount_amount'] = formatDecimal($item['discount_amount'] * $item['quantity']);
                    $item['net_cost'] = $item['cost'] - $item['discount_amount'];
                    $item['taxes'] = (new TaxAction())->calculate($item['taxes'] ?? false, $item['net_cost'], $item['quantity'], !empty($item['tax_included']));
                    if (!empty($item['tax_included']) && $item['taxes']) {
                        $item['net_cost'] -= $item['taxes']->sum('amount');
                    }
                    $item['tax_amount'] = $item['taxes'] ? formatDecimal($item['taxes']->sum('amount')) : 0;
                    $item['total_tax_amount'] = formatDecimal($item['tax_amount'] * $item['quantity']);
                    $item['unit_cost'] = formatDecimal($item['net_cost'] + $item['tax_amount']);
                    $item['subtotal'] = formatDecimal(($item['quantity'] * $item['unit_cost']), 2);
                }
                if ($purchase) {
                    if (isset($item['unit_id']) && $item['unit_id'] != $item['item_unit_id'] && $unit = Unit::find($item['unit_id'])) {
                        $item['balance'] = convert_to_base_quantity($item['quantity'], $unit);
                        $item['base_net_cost'] = calculate_base_costing($item['net_cost'], $unit);
                        $item['base_unit_cost'] = calculate_base_costing($item['unit_cost'], $unit);
                    } else {
                        $item['balance'] = $item['quantity'];
                        $item['base_net_cost'] = $item['net_cost'];
                        $item['base_unit_cost'] = $item['unit_cost'];
                    }
                }
                if ($adjustment) {
                    if (isset($item['unit_id']) && $item['unit_id'] != $item['item_unit_id'] && $unit = Unit::find($item['unit_id'])) {
                        $item['base_net_cost'] = calculate_base_costing($item['net_cost'], $unit);
                        $item['base_unit_cost'] = calculate_base_costing($item['unit_cost'], $unit);
                    } else {
                        $item['base_net_cost'] = $item['net_cost'];
                        $item['base_unit_cost'] = $item['unit_cost'];
                    }
                }
            } else {
                $price = 0;
                $subtotal = 0;
                $tax_amount = $total_tax_amount = 0;
                $discount_amount = $total_discount_amount = 0;
                if (!empty($item['selected']['serials'])) {
                    $item['serials'] = $item['selected']['serials'];
                }
                if (!empty($item['selected']['modifiers'])) {
                    $options = [];
                    $mIds = collect($item['selected']['modifiers'])->pluck('selected');
                    $selectedModifierOption = ModifierOption::whereIn('id', $mIds)->get();
                    foreach ($item['selected']['modifiers'] as $m) {
                        // $option = ModifierOption::find($m['selected']);
                        // $modifiter = Modifier::find($m['id']);
                        // $option = $modifiter->options->where('id', $m['selected']);
                        $option = $selectedModifierOption->find($m['selected']);
                        $option->price = $option->item->price;
                        $options[$option->id] = (new OrderItemService())($m, $item, $option);
                        $options[$option->id]['modifier_id'] = $m['id'];
                        $price += $options[$option->id]['price'];
                        // $price += $options[$option->id]['net_price'];
                        $tax_amount += $options[$option->id]['tax_amount'];
                        $discount_amount += $options[$option->id]['discount_amount'];
                        $total_tax_amount += $options[$option->id]['total_tax_amount'];
                        $total_discount_amount += $options[$option->id]['total_discount_amount'];
                        $subtotal += $options[$option->id]['total'];
                    }
                    $item['modifierOptions'] = $options;
                }
                if (!empty($item['selected']['variations']) || !empty($item['selected']['portions'])) {
                    if (!empty($item['selected']['variations'])) {
                        $variations = [];
                        $vIds = collect($item['selected']['variations'])->pluck('id');
                        $selectedVariations = Variation::whereIn('id', $vIds)->with('locationStock')->get();
                        foreach ($item['selected']['variations'] as $v) {
                            // $variation = Variation::find($v['id']);
                            $variation = $selectedVariations->find($v['id']);
                            $variations[$variation->id] = (new OrderItemService())($v, $item, $variation, true);
                            $price += $variations[$variation->id]['price'];
                            // $price += $variations[$variation->id]['net_price'];
                            $tax_amount += $variations[$variation->id]['tax_amount'];
                            $discount_amount += $variations[$variation->id]['discount_amount'];
                            $total_tax_amount += $variations[$variation->id]['total_tax_amount'];
                            $total_discount_amount += $variations[$variation->id]['total_discount_amount'];
                            $subtotal += $variations[$variation->id]['total'];
                        }
                        $item['variations'] = $variations;
                    }
                    if (!empty($item['selected']['portions'])) {
                        $portions = [];
                        $pIds = collect($item['selected']['portions'])->pluck('id');
                        $selectedPortions = Portion::whereIn('id', $pIds)->get();
                        foreach ($item['selected']['portions'] as $p) {
                            $portion = $selectedPortions->find($p['id']);
                            $selected = (new OrderItemService())($p, $item, $portion);
                            $selected['portion_id'] = $portion->id;
                            $price += $selected['unit_price'];
                            $tax_amount += $selected['tax_amount'];
                            $discount_amount += $selected['discount_amount'];
                            $total_tax_amount += $selected['total_tax_amount'];
                            $total_discount_amount += $selected['total_discount_amount'];
                            $subtotal += $selected['total'];
                            $portions[] = $selected;
                        }
                        $item['portions'] = $portions;
                    }
                    $item['price'] = $price;
                    $item['tax_amount'] = $tax_amount;
                    $item['discount_amount'] = $discount_amount;
                    $item['total_tax_amount'] = $total_tax_amount;
                    $item['total_discount_amount'] = $total_discount_amount;

                    $item['net_price'] = $item['price'] - $item['discount_amount'];
                    // $item['taxes'] = (new TaxAction)->calculate($item['taxes'] ?? false, $item['net_price'], $item['quantity']);
                    // $item['tax_amount'] = $item['taxes'] ? formatDecimal($item['taxes']->sum('amount')) : 0;
                    // $item['total_tax_amount'] = formatDecimal($item['tax_amount'] * $item['quantity']);
                    // $item['unit_price'] = formatDecimal($item['net_price'] + $item['tax_amount']);  // TODO check for normal discount
                    $item['unit_price'] = formatDecimal($item['net_price'] + (isset($item['item_promotions']) ? $item['discount_amount'] : 0) + $item['tax_amount']);
                    $item['subtotal'] = formatDecimal($subtotal, 2);
                    $item['item_promotions'] = $item['item_promotions'] ?? $item['allPromotions'] ?? null;
                } else {
                    // $item['price'] += $price;
                    $item['discount_amount'] = (new DiscountAction())->calculate($item['discount'], $item['price']);
                    // TODO apply promotions
                    if (!empty($item['promotions'])) {
                        $promotions = Promotion::whereIn('id', $item['promotions'])->get();
                        foreach ($promotions as $promotion) {
                            if ($promotion['type'] != 'BXGY') {
                                $item['discount_amount'] += (new DiscountAction())->calculate($promotion['discount'], ($item['price'] - $item['discount_amount']));
                            }
                        }
                    }
                    $item['price'] += $price;
                    $item['discount_amount'] += $discount_amount;
                    $item['total_discount_amount'] = formatDecimal($item['discount_amount'] * $item['quantity']);
                    $item['total_discount_amount'] += $total_discount_amount;
                    $item['net_price'] = $item['price'] - $item['discount_amount'];
                    $item['taxes'] = (new TaxAction())->calculate($item['taxes'] ?? false, $item['net_price'], $item['quantity'], !empty($item['tax_included']));
                    if (!empty($item['tax_included']) && $item['taxes']) {
                        $item['net_price'] -= $item['taxes']->sum('amount');
                    }
                    $item['tax_amount'] = $item['taxes'] ? formatDecimal($item['taxes']->sum('amount')) : 0;
                    $item['total_tax_amount'] = formatDecimal($item['tax_amount'] * $item['quantity']);
                    $item['unit_price'] = formatDecimal($item['net_price'] + $item['tax_amount']);
                    $item['subtotal'] = formatDecimal(($item['quantity'] * $item['unit_price']), 2);
                    $item['item_promotions'] = $item['item_promotions'] ?? $item['allPromotions'] ?? null;
                }
            }
            unset($item['selected']);
            $item['order'] = $index;
            $item['item_taxes'] = $item['item_taxes'] ?? $item['allTaxes'] ?? null;
            return $item;
        });

        $v['items']            = $items;
        $v['total']            = formatDecimal($v['items']->sum('subtotal'), 2);
        $v['item_tax_amount']  = formatDecimal($items->sum('total_tax_amount'), 2);
        $v['discount_amount']  = (new DiscountAction())->calculate($v['discount'] ?? false, $v['total']);
        $v['taxes']            = (new TaxAction())->calculate($v['taxes'] ?? false, ($v['total'] - $v['discount_amount']));
        $v['order_tax_amount'] = $v['taxes'] ? formatDecimal($v['taxes']->sum('amount'), 2) : 0;
        $v['total_tax_amount'] = formatDecimal($v['item_tax_amount'] + $v['order_tax_amount'], 2);
        $v['grand_total']      = formatDecimal($v['total'] - $v['discount_amount'] + $v['order_tax_amount'] + ($v['shipping'] ?? 0), 2);

        // TODO Caclculate recoverable taxes
        // $rT                                 = (new TaxAction)->recoverableTaxAmount($v['taxes']);
        // $v['recoverable_tax_amount']        = $rT['recoverable_tax_amount'];
        // $v['recoverable_tax_calculated_on'] = $rT['recoverable_tax_calculated_on'];
        // foreach ($items as $item) {
        //     $rT = (new TaxAction)->ecoverableTaxAmount($item['taxes']);
        //     $v['recoverable_tax_amount']        += $rT['recoverable_tax_amount'];
        //     $v['recoverable_tax_calculated_on'] += $rT['recoverable_tax_calculated_on'];
        // }
        // dump($v);
        return $v;
    }
}

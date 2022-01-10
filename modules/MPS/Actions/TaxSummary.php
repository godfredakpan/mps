<?php

namespace Modules\MPS\Actions;

class TaxSummary
{
    public static function calculate($sale, $byCost = false)
    {
        $taxes = collect([]);
        if ($sale->items->isNotEmpty()) {
            foreach ($sale->items as $item) {
                if ($item->taxes->isNotEmpty()) {
                    foreach ($item->taxes as $tax) {
                        $existingTax = $taxes->where('id', $tax->id)->first();
                        $totalNet    = formatDecimal(($byCost ? $item->net_cost : $item->net_price) * $item->quantity);
                        $amount      = formatDecimal(($totalNet * $tax->rate) / 100);
                        if ($existingTax) {
                            $existingTax['amount']          += $amount;
                            $existingTax['quantity']        += $item->quantity;
                            $existingTax['item_net_amount'] += $totalNet;
                            $existingTax['item_tax_amount'] += $item->total_tax_amount;
                            $taxes->transform(function ($summaryTax) use ($tax, $item, $amount, $totalNet) {
                                if ($summaryTax['id'] == $tax->id) {
                                    $summaryTax['amount'] = $summaryTax['amount'] + $amount;
                                    $summaryTax['quantity'] = $summaryTax['quantity'] + $item->quantity;
                                    $summaryTax['item_net_amount'] = $summaryTax['item_net_amount'] + $totalNet;
                                    $summaryTax['item_tax_amount'] = $summaryTax['item_tax_amount'] + $item->total_tax_amount;
                                    return $summaryTax;
                                }
                                return $tax;
                            });
                        } else {
                            $taxes->push([
                                'id'              => $tax->id,
                                'code'            => $tax->code,
                                'name'            => $tax->name,
                                'rate'            => $tax->rate,
                                'amount'          => $amount,
                                'number'          => $tax->number,
                                'quantity'        => $item->quantity,
                                'item_net_amount' => $totalNet,
                                'item_tax_amount' => $item->total_tax_amount,
                            ]);
                        }
                    }
                }
            }
        }
        if ($sale->taxes->isNotEmpty()) {
            foreach ($sale->taxes as $tax) {
                $existingTax = $taxes->where('id', $tax->id)->first();
                $amount      = formatDecimal(($sale->total * $tax->rate) / 100);
                if ($existingTax) {
                    $taxes->transform(function ($summaryTax) use ($tax, $sale, $amount, $totalNet) {
                        if ($summaryTax['id'] == $tax->id) {
                            $summaryTax['amount'] += $amount;
                            $summaryTax['item_net_amount'] += $totalNet;
                            $summaryTax['item_tax_amount'] += $sale->order_tax_amount;
                            return $summaryTax;
                        }
                        return $tax;
                    });
                } else {
                    $taxes->push([
                        'id'              => $tax->id,
                        'code'            => $tax->code,
                        'name'            => $tax->name,
                        'rate'            => $tax->rate,
                        'amount'          => $amount,
                        'number'          => $tax->number,
                        'item_net_amount' => $totalNet,
                        'item_tax_amount' => $sale->order_tax_amount,
                    ]);
                }
            }
        }
        return $taxes;
    }
}

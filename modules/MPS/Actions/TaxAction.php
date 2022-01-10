<?php

namespace Modules\MPS\Actions;

use Modules\MPS\Models\Tax;

class TaxAction
{
    public function calculate($taxes, $amount, $quantity = 1, $tax_included = null, $full_data = false)
    {
        if (empty($taxes)) {
            return null;
        }

        $data                      = [];
        $compoundTaxes             = [];
        $totalCompoundTaxAmount    = 0;
        $totalNonCompoundTaxAmount = 0;
        if (!$full_data) {
            $taxes = Tax::whereIn('id', $taxes)->get();
        }
        $compoundTaxes    = $taxes->where('compound', 1);
        $nonCompoundTaxes = $taxes->where('compound', 0);

        if ($tax_included) {
            foreach ($compoundTaxes as $tax) {
                $calculated_on = $amount;
                $taxAmount     = formatDecimal(($calculated_on * $tax->rate) / (100 + $tax->rate));
                $totalCompoundTaxAmount += $taxAmount;
                $data[$tax->id] = [
                    'amount'        => $taxAmount,
                    'calculated_on' => $calculated_on,
                    'recoverable'   => $tax->recoverable,
                    'total_amount'  => formatDecimal($taxAmount * $quantity),
                ];
            }
            foreach ($nonCompoundTaxes as $tax) {
                $calculated_on  = $amount - $totalCompoundTaxAmount;
                $taxAmount      = formatDecimal(($calculated_on * $tax->rate) / (100 + $tax->rate));
                $data[$tax->id] = [
                    'amount'        => $taxAmount,
                    'calculated_on' => $calculated_on,
                    'recoverable'   => $tax->recoverable,
                    'total_amount'  => formatDecimal($taxAmount * $quantity),
                ];
            }
        } else {
            foreach ($nonCompoundTaxes as $tax) {
                $calculated_on = $amount;
                $taxAmount     = formatDecimal($amount * $tax->rate / 100);
                $totalNonCompoundTaxAmount += $taxAmount;
                $data[$tax->id] = [
                    'amount'        => $taxAmount,
                    'calculated_on' => $calculated_on,
                    'recoverable'   => $tax->recoverable,
                    'total_amount'  => formatDecimal($taxAmount * $quantity),
                ];
            }
            foreach ($compoundTaxes as $tax) {
                $calculated_on  = $amount + $totalNonCompoundTaxAmount;
                $taxAmount      = formatDecimal($calculated_on * $tax->rate / 100);
                $data[$tax->id] = [
                    'amount'        => $taxAmount,
                    'calculated_on' => $calculated_on,
                    'recoverable'   => $tax->recoverable,
                    'total_amount'  => formatDecimal($taxAmount * $quantity),
                ];
            }
        }

        return collect($data);
    }

    public static function calculateTax($tax, $amount, $tax_included = null)
    {
        if (!empty($tax) && !empty($amount)) {
            if ($tax_included) {
                return formatDecimal(($amount * $tax->rate) / (100 + $tax->rate));
            }
            return formatDecimal($amount * $tax->rate / 100);
        }
        return null;
    }

    public function recoverableTaxAmount($taxes)
    {
        $fT = $taxes ? $taxes->where('recoverable', true) : null;
        if (!empty($fT)) {
            return [
                'recoverable_tax_amount'        => $fT->sum('total_amount'),
                'recoverable_tax_calculated_on' => $fT->sum('calculated_on'),
            ];
        }
        return ['recoverable_tax_amount' => 0, 'recoverable_tax_calculated_on' => 0];
    }
}

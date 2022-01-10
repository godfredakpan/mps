<?php

namespace Modules\MPS\Actions;

class DiscountAction
{
    public static function calculate($discount = 0, $amount = 0)
    {
        if ($discount && $amount) {
            $discount = formatDecimal((($amount * (float) $discount) / 100), 2);
        }
        return $discount;
    }
}

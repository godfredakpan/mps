<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Sequence::class, function (Faker $faker) {
    return [
        'sale'             => 0,
        'order'            => 0,
        'income'           => 0,
        'salary'           => 0,
        'account'          => 0,
        'expense'          => 0,
        'payment'          => 0,
        'delivery'         => 0,
        'purchase'         => 0,
        'quotation'        => 0,
        'return_order'     => 0,
        'recurring_sale'   => 0,
        'asset_transfer'   => 0,
        'stock_transfer'   => 0,
        'stock_adjustment' => 0,
        // 'last_reset_date'  => now(),
    ];
});

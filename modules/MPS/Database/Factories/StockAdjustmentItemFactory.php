<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\StockAdjustmentItem::class, function (Faker $faker) {
    return [
        'code'           => $faker->unique()->ean13,
        'name'           => $faker->words(mt_rand(2, 5), true),
        'quantity'       => $qty = $faker->numberBetween(1, 5),
        'cost'           => $cost = $faker->numberBetween(10, 50),
        'net_cost'       => $cost,
        'unit_cost'      => $cost,
        'base_net_cost'  => $cost,
        'base_unit_cost' => $cost,
        'discount'       => null,
        'subtotal'       => ($cost * $qty),
        'batch_no'       => $faker->bothify('********'),
        'expiry_date'    => $faker->dateTimeBetween('-3 months', '+2 years')->format('Y-m-d'),
    ];
});

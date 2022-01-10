<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\ReturnOrderItem::class, function (Faker $faker) {
    return [
        'code'        => $faker->unique()->ean13,
        'name'        => $faker->words(mt_rand(2, 5), true),
        'quantity'    => $qty = $faker->numberBetween(1, 3),
        'price'       => $price = $faker->numberBetween(10, 50),
        'unit_price'  => $price,
        'net_price'   => $price,
        'discount'    => null,
        'subtotal'    => ($price * $qty),
        'batch_no'    => $faker->bothify('********'),
        'expiry_date' => $faker->date($format = 'Y-m-d', $max = '+ 2 years'),
        // 'cost'        => $price / 2,
    ];
});

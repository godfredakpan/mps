<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\VariationStock::class, function (Faker $faker) {
    $cost = $faker->numberBetween(2, 10);
    return [
        'cost'     => $cost,
        'price'    => $cost * 1.5,
        'quantity' => mt_rand(5, 10),
        // 'location_id'  => '',
        // 'variation_id' => '',
    ];
});

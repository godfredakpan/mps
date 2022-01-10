<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Variation::class, function (Faker $faker) {
    $cost = $faker->numberBetween(2, 10);
    $n = $faker->unique()->numberBetween(1, 1000);
    return [
        'sku'      => sku(),
        'cost'     => $cost,
        'price'    => $cost * 1.5,
        'code'     => 'Var ' . $n,
        'quantity' => mt_rand(5, 10),
        // 'name'     => 'Variation ' . $n,
        // 'item_id'  => '',
        // 'meta'     => [],
    ];
});

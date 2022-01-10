<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Ingredient::class, function (Faker $faker) {
    $cost = $faker->numberBetween(2, 10);
    $n = $faker->unique()->numberBetween(1, 100);
    return [
        'sku'      => sku(),
        'cost'     => $cost,
        'code'     => 'ING ' . $n,
        'quantity' => mt_rand(5, 10),
        'name'     => 'Ingredient ' . $n,
    ];
});

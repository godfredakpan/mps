<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\StockAdjustment::class, function (Faker $faker) {
    $type = ['addition', 'damage', 'subtraction'];
    return [
        'date'        => $faker->dateTimeBetween('-3 months'),
        'draft'       => $faker->boolean,
        'total'       => $faker->numberBetween(1, 50),
        'user_id'     => $faker->numberBetween(1, 3),
        'grand_total' => $faker->numberBetween(1, 50),
        'details'     => $faker->sentence(mt_rand(5, 8)),
        'type'        => $faker->randomElement($type),
    ];
});

<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Sale::class, function (Faker $faker) {
    return [
        'date'        => $faker->dateTimeBetween('-3 months'),
        'total'       => $total = $faker->numberBetween(1, 50),
        'user_id'     => $faker->numberBetween(1, 3),
        'customer_id' => $faker->numberBetween(1, 30),
        'grand_total' => $total,
        'details'     => $faker->sentence(mt_rand(5, 8)),
        'type'        => 'sale',
        'discount'    => null,
        'shipping'    => null,
    ];
});

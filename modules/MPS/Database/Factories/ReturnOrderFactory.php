<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\ReturnOrder::class, function (Faker $faker) {
    $types = ['sale', 'purchase'];
    return [
        'date'        => $faker->dateTimeBetween('-3 months'),
        'total'       => $faker->numberBetween(1, 50),
        'user_id'     => $faker->numberBetween(1, 3),
        'customer_id' => $faker->numberBetween(1, 30),
        'grand_total' => $faker->numberBetween(1, 50),
        'reference'   => \Ulid\Ulid::generate(true),
        'details'        => $faker->sentence(mt_rand(5, 8)),
        'type'        => $types[mt_rand(0, 1)],
        'discount'    => null,
        'shipping'    => null,
    ];
});

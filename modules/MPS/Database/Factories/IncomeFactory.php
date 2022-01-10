<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Income::class, function (Faker $faker) {
    $day = mt_rand(0, 7);
    return [
        'title'   => $faker->name,
        'details' => $faker->text(50, 120),
        'amount'  => $faker->numberBetween(10, 100),
        'date'    => $faker->dateTimeBetween('-3 months'),
    ];
});

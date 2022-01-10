<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Salary::class, function (Faker $faker) {
    return [
        'advance' => false,
        'status'  => 'paid',
        'type'    => 'salary',
        'details' => $faker->sentence(mt_rand(5, 8)),
        'amount'  => $faker->numberBetween(999, 3000),
        'date'    => $faker->dateTimeBetween('-3 months'),
    ];
});

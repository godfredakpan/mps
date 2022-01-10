<?php

use Faker\Generator as Faker;

/* @var Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Modules\MPS\Models\Expense::class, function (Faker $faker) {
    $day = mt_rand(0, 7);
    return [
        'approved_by_id' => null,
        'title'          => $faker->name,
        'approved'       => $faker->boolean,
        'details'        => $faker->text(50, 120),
        'amount'         => $faker->numberBetween(10, 100),
        'date'           => $faker->dateTimeBetween('-3 months'),
    ];
});

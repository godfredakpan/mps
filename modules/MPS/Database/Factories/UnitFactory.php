<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Unit::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->word,
        'name' => $faker->words(mt_rand(1, 3), true),
    ];
});

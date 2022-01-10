<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Serial::class, function (Faker $faker) {
    return [
        'number' => $faker->unique()->ean13,
    ];
});

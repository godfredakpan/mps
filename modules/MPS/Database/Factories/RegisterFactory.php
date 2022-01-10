<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Register::class, function (Faker $faker) {
    return [
        'code' => $faker->unique()->ean8,
        'name' => $faker->unique()->numerify('Register ###'),
    ];
});

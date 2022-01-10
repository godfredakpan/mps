<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Payment::class, function (Faker $faker) {
    return [
        'details' => $faker->text(50, 120),
    ];
});

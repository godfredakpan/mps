<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Setting::class, function (Faker $faker) {
    return [
        'mps_key'   => 1,
        'mps_value' => 1,
    ];
});

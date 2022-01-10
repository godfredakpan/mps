<?php

use Faker\Generator as Faker;

$factory->define(App\Setting::class, function (Faker $faker) {
    return [
        'tec_key'   => 1,
        'tec_value' => 1,
    ];
});

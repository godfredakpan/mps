<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\AssetTransfer::class, function (Faker $faker) {
    return [
        'user_id' => 1,
        'to'      => mt_rand(1, 5),
        'from'    => mt_rand(1, 5),
        'details' => $faker->text(100, 200),
        'amount'  => $faker->numberBetween(10, 100),
    ];
});

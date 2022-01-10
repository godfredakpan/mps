<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\StockTransfer::class, function (Faker $faker) {
    $status = ['pending', 'transferring', 'transferred'];
    return [
        'user_id' => 1,
        'to'      => mt_rand(1, 5),
        'from'    => mt_rand(1, 5),
        'details' => $faker->text(100, 200),
        'status'  => $faker->randomElement($status),
    ];
});

<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\PortionEssential::class, function (Faker $faker) {
    return [
        // 'item_id'      => '',
        // 'portion_id'   => '',
        // 'variation_id' => '',
        'quantity' => mt_rand(1, 2),
    ];
});

<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\PortionItem::class, function (Faker $faker) {
    return [
        'quantity' => mt_rand(1, 3),
        // 'item_id'  => '',
        // 'portion_id' => '',
    ];
});

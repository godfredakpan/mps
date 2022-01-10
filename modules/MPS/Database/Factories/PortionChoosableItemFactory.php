<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\PortionChoosableItem::class, function (Faker $faker) {
    return [
        // 'item_id'              => '',
        // 'portion_choosable_id' => '',
        // 'variation_id'         => '',
        'quantity' => mt_rand(1, 2),
    ];
});

<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Portion::class, function (Faker $faker) {
    $cost = $faker->numberBetween(2, 10);
    return [
        'sku'   => sku(),
        'cost'  => $cost,
        'price' => $cost * 1.5,
        'name'  => 'regular',
        // 'item_id'  => '',
    ];
});

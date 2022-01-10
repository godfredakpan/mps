<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\ModifierOption::class, function (Faker $faker) {
    return [
        'sku'          => sku(),
        'item_id'      => null,
        'variation_id' => null,
        'quantity'     => 1,
    ];
});

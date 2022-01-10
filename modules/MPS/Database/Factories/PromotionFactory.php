<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Promotion::class, function (Faker $faker) {
    return [
        'active'          => true,
        'type'            => 'simple',
        'name'            => 'Simple',
        'discount_method' => 'percentage',
        'discount'        => $faker->numberBetween(5, 15),
    ];
});

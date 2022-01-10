<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\PortionChoosable::class, function (Faker $faker) {
    return [
        // 'item_id'      => '',
        // 'portion_id'   => '',
        'name' => $faker->unique()->numerify('Choosable Group ###'),
    ];
});

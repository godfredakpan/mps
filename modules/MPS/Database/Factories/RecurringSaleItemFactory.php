<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\RecurringSaleItem::class, function (Faker $faker) {
    return [
        'batch_no'    => $faker->bothify('********'),
        'expiry_date' => $faker->date($format = 'Y-m-d', $max = '+ 2 years'),
    ];
});

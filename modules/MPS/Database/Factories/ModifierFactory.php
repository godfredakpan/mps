<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Modifier::class, function (Faker $faker) {
    $showAs = ['radio', 'checkbox', 'select', 'select_multiple'];
    $n = $faker->unique()->numberBetween(5, 1000);
    return [
        'code'    => 'MOD' . $n,
        'title'   => 'Modifier ' . $n,
        'show_as' => $faker->randomElement($showAs),
    ];
});

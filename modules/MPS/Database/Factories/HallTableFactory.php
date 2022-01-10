<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\HallTable::class, function (Faker $faker) {
    static $number = 0;
    $number++;
    return [
        'code'  => 'T' . ($number < 10 ? '0' . $number : $number),
        'title' => 'Table ' . ($number < 10 ? '0' . $number : $number),
    ];
});

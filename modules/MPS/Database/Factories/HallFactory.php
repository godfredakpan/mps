<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Hall::class, function (Faker $faker) {
    static $number = 0;
    $number++;
    return [
        'code'  => 'H' . ($number < 10 ? '0' . $number : $number),
        'title' => 'Hall ' . ($number < 10 ? '0' . $number : $number),
    ];
});

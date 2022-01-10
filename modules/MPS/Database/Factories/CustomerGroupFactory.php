<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\CustomerGroup::class, function (Faker $faker) {
    static $number = 0;
    $number++;
    return [
        'discount' => mt_rand(10, 30),
        'code'     => 'CG' . ($number < 10 ? '0' . $number : $number),
        'name'     => 'CustomerGroup ' . ($number < 10 ? '0' . $number : $number),
    ];
});

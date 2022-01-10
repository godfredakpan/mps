<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Brand::class, function (Faker $faker) {
    static $number = 0;
    $number++;
    return [
        'details' => $faker->text(50, 120),
        'photo'   => url('images/dummy.jpg'),
        'code'    => 'B' . ($number < 10 ? '0' . $number : $number),
        'name'    => $name = 'Brand ' . ($number < 10 ? '0' . $number : $number),
        'slug'    => \Illuminate\Support\Str::slug($name),
    ];
});

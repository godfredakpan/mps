<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Category::class, function (Faker $faker) {
    static $number = 0;
    $number++;
    return [
        'parent_id' => null,
        'photo'     => url('images/dummy.jpg'),
        'code'      => 'C' . ($number < 10 ? '0' . $number : $number),
        'name'      => $name = 'Category ' . ($number < 10 ? '0' . $number : $number),
        'slug'      => \Illuminate\Support\Str::slug($name),
    ];
});

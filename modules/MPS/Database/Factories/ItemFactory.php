<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Item::class, function (Faker $faker) {
    static $number = 5;
    $number++;
    $cost = $faker->numberBetween(5, 100);
    return [
        'max_discount' => 30,
        'sku'          => sku(),
        'cost'         => $cost,
        'is_stock'     => false,
        'symbology'    => 'ean13',
        'type'         => 'standard',
        'price'        => $cost * 1.5,
        'min_price'    => $cost * 1.1,
        'max_price'    => $cost * 1.8,
        'changeable'   => $faker->boolean,
        'expiry'       => $faker->boolean,
        'tax_included' => $faker->boolean,
        'summary'      => $faker->text(200),
        'code'         => $faker->unique()->ean13,
        'photo'        => url('images/dummy.jpg'),
        'details'      => $faker->paragraph(mt_rand(3, 5)),
        'rack'         => $faker->numerify('Rack ###'),
        'alt_name'     => $faker->unique()->bothify('Product ???###'),
        'name'         => $name = 'Item ' . ($number < 10 ? '0' . $number : $number),
        'slug'         => \Illuminate\Support\Str::slug($name),
    ];
});

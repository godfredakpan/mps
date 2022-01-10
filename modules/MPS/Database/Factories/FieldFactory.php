<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Field::class, function (Faker $faker) {
    $name = $faker->unique()->numerify('Custom Field 0#');
    $types = ['text', 'textarea', 'date', 'datetime', 'radio', 'checkbox', 'select', 'number'];
    $type = $faker->randomElement($types);
    $options = $type == 'select' || $type == 'radio' || $type == 'checkbox' ? 'Option 1|Option 2|Option 3' : '';
    return [
        'order'       => 1,
        'required'    => 0,
        'name'        => $name,
        'type'        => $type,
        'options'     => $options,
        'description' => $faker->text,
        'slug'        => \Illuminate\Support\Str::slug($name, '_'),
        'entities'    => ['asset_transfer', 'customer', 'delivery', 'expense', 'income', 'item', 'location', 'payment', 'purchase', 'quotation', 'return_order', 'sale', 'stock_adjustment', 'stock_transfer', 'supplier'],
    ];
});

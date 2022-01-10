<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\StockTransferItem::class, function (Faker $faker) {
    $status = ['pending', 'transferring', 'transferred'];
    return [
        'code'              => '',
        'name'              => '',
        'stock_transfer_id' => '',
        'item_id'           => '',
        'unit_id'           => '',
        'quantity'          => $qty = $faker->numberBetween(1, 3),
        'batch_no'          => $faker->bothify('********'),
        'expiry_date'       => $faker->date($format = 'Y-m-d', $max = '+ 2 years'),
    ];
});

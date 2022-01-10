<?php

use Faker\Generator as Faker;
use Modules\MPS\Models\Account;

$factory->define(Account::class, function (Faker $faker) {
    return [
        'name'            => $faker->name,
        'type'            => $faker->word,
        'reference'       => $faker->word,
        'offline'         => $faker->boolean,
        'details'         => $faker->text(100, 200),
        'opening_balance' => $faker->numberBetween(1000, 10000),
    ];
});

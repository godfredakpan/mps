<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Company::class, function (Faker $faker) {
    return [
        'country'      => 'IN',
        'state'        => 'IN-DL',
        'state_name'   => 'Delhi',
        'country_name' => 'India',
        'number'       => $faker->isbn10,
        'email'        => $faker->safeEmail,
        'footer'       => $faker->text(160),
        'logo'         => 'images/default.png',
        'address'      => $faker->streetAddress,
        'phone'        => $faker->e164PhoneNumber,
        'name'         => $faker->company . ' ' . $faker->companySuffix,
    ];
});

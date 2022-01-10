<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Supplier::class, function (Faker $faker) {
    $earth = new \MenaraSolutions\Geographer\Earth();
    $state = collect($earth->findOneByCode('IN')->getStates()->toArray())->transform(function ($item) {
        return ['value' => $item['isoCode'], 'label' => $item['name']];
    })->random();

    return [
        'country'      => 'IN',
        'country_name' => 'India',
        'name'         => $faker->name,
        'company'      => $faker->company,
        'state'        => $state['value'],
        'state_name'   => $state['label'],
        'email'        => $faker->safeEmail,
        'phone'        => $faker->phoneNumber,
        'address'      => $faker->streetAddress,
    ];
});

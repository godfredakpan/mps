<?php

use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Location::class, function (Faker $faker) {
    $earth = new \MenaraSolutions\Geographer\Earth();
    $colors = ['#FFCC00', '#CCFF33', '#33FFDD', '#33FFFF', '#FF33DD'];
    $state = collect($earth->findOneByCode('IN')->getStates()->toArray())->transform(function ($item) {
        return ['value' => $item['isoCode'], 'label' => $item['name']];
    })->random();

    return [
        'country'      => 'IN',
        'country_name' => 'India',
        'address'      => $faker->address,
        'state'        => $state['value'],
        'state_name'   => $state['label'],
        'email'        => $faker->safeEmail,
        'name'         => $state['label'],
        'phone'        => $faker->e164PhoneNumber,
        'color'        => $faker->randomElement($colors),
    ];
});

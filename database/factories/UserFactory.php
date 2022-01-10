<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'active'            => 1,
        'locale'            => 'en',
        'name'              => $faker->name,
        'email'             => $faker->unique()->safeEmail,
        'username'          => $faker->unique()->userName,
        'password'          => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token'    => Str::random(10),
        'can_impersonate'   => $faker->boolean,
        'view_all'          => 0,
        'edit_all'          => 0,
        'bulk_actions'      => 0,
        'can_impersonate'   => 1,
        'email_verified_at' => now(),
    ];
});

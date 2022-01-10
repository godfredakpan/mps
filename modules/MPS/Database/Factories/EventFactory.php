<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Modules\MPS\Models\Event::class, function (Faker $faker) {
    $colors = ['', 'orange', 'blue', 'red', 'green', 'purple'];
    $ld = new Carbon('last day of this month');
    $sd = $ld->subDays(mt_rand(0, 28));
    $ed = new Carbon($sd);
    $ed = $faker->boolean(25) ? $ed->addDays(mt_rand(1, 2)) : null;
    $start_time = now()->subMinutes(mt_rand(20, 300));
    $end_time = now()->addMinutes(mt_rand(60, 120));
    return [
        'end_date'   => $ed,
        'start_date' => $sd,
        'start_time' => $start_time->format('H:i:s'),
        'end_time'   => $end_time->format('H:i:s'),
        'user_id'    => mt_rand(1, 5),
        'details'    => $faker->paragraph(),
        'title'      => $faker->sentence(mt_rand(3, 6)),
        'color'      => $faker->boolean(40) ? '' : $colors[mt_rand(0, 5)],
    ];
});

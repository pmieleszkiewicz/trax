<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Trip;
use Faker\Generator as Faker;

$factory->define(Trip::class, function (Faker $faker) {
    $miles = $faker->randomFloat(1, 1, 100);

    return [
        'took_place_at' => $faker->dateTimeBetween('-3 years')->format('Y-m-d'),
        'miles' => $faker->randomFloat(1, 1, 100),
        'miles_balance' => $miles,
    ];
});

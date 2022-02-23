<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Car;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$brandsWithModels = collect([
    'fiat' => ['bravo', 'stilo', 'panda', 'tipo'],
    'alfa romeo' => ['giulietta', 'giulia', '159', 'mito', 'stelvio'],
    'honda' => ['civic', 'accord'],
    'toyota' => ['corolla', 'rav-4', 'auris'],
    'opel' => ['corsa', 'astra'],
    'mazda' => ['3', '6', '2'],
]);

$factory->define(Car::class, function (Faker $faker) use ($brandsWithModels) {
    $brand = $brandsWithModels->keys()->random();
    $model = collect($brandsWithModels[$brand])->random();
    $year = $faker->numberBetween(2010, now()->year);

    return [
        'make' => Str::title($brand),
        'model' => Str::title($model),
        'year' => $year,
    ];
});

<?php

use App\Car;
use App\Trip;
use App\User;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();

        $user->cars->each(function (Car $car) use ($user) {
            factory(Trip::class, 2)->create([
                'user_id' => $user->id,
                'car_id' => $car->id,
            ]);
        });
    }
}

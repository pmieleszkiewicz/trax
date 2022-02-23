<?php

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
        $cars = $user->cars;

        factory(Trip::class, 10)->create([
            'user_id' => $user->id,
            'car_id' => $cars->random()->id,
        ]);
    }
}

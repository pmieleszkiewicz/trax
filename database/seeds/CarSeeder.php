<?php

use App\Car;
use App\User;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();

        factory(Car::class, 5)->create([
            'user_id' => $user->id,
        ]);
    }
}

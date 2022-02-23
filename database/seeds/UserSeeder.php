<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample user to test out application
        factory(User::class)->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);
    }
}

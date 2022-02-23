<?php

namespace Tests\Feature;

use App\Car;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class CarTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_authorized_users_can_access_cars()
    {
        // When I want to access my cars being unauthorized/not logged in
        $response = $this->json('GET', '/api/cars');

        // Then I should get 401 response - Unauthorized
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logged_user_can_only_get_cars_which_belong_to_them()
    {
        // Given two users with cars
        $user = factory(User::class)->create();
        $usersCars = factory(Car::class, 2)->create([
            'user_id' => $user->id,
        ]);

        $otherUser = factory(User::class)->create();
        factory(Car::class, 2)->create([
            'user_id' => $otherUser->id,
        ]);

        // When I'm logged as $user and want to get my cars
        $this->actingAs($user, 'api');
        $response = $this->json('GET', '/api/cars');

        // Then I should get only my cars
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'data');

        $data = collect($response['data']);
        $this->assertEquals($usersCars->pluck('id'), $data->pluck('id'));
    }
}

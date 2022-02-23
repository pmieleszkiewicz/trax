<?php

namespace Tests\Feature;

use App\Car;
use App\Trip;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_authorized_users_can_list_trips()
    {
        // When I want to access trips being unauthorized/not logged in
        $response = $this->json('GET', route('trips.index'));

        // Then I should get 401 response - Unauthorized
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logged_user_can_only_get_trips_which_belong_to_them()
    {
        // Given two users with cars
        $user = factory(User::class)->create();
        $userCar = factory(Car::class)->create(['user_id' => $user->id]);
        $userTrips = factory(Trip::class, 2)->create(['user_id' => $user->id, 'car_id' => $userCar->id]);

        $otherUser = factory(User::class)->create();
        $otherUserCar = factory(Car::class)->create(['user_id' => $otherUser->id]);
        $otherUserTrips = factory(Trip::class, 2)->create(['user_id' => $otherUser->id, 'car_id' => $otherUserCar->id]);

        // When I'm logged as $user and want to get my trips
        $this->actingAs($user, 'api');
        $response = $this->json('GET', route('trips.index'));

        // Then I should get only my trips
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'data');

        $data = collect($response['data']);
        $this->assertEquals($userTrips->pluck('id'), $data->pluck('id'));
    }
}

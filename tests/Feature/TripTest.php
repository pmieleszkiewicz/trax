<?php

namespace Tests\Feature;

use App\Car;
use App\Trip;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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

        $this->assertEquals(
            $userTrips->pluck('id')->sort()->values(),
            $data->pluck('id')->sort()->values(),
        );
    }

    public function test_only_authorized_users_can_create_trips()
    {
        // When I want to create a new trip being unauthorized/not logged in
        $response = $this->json('POST', route('trips.store'), []);

        // Then I should get 401 response - Unauthorized
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logged_user_can_create_new_trip_when_data_is_valid()
    {
        // Given registered user
        $user = factory(User::class)->create();
        $car = factory(Car::class)->create([
            'user_id' => $user->id,
        ]);



        // When I'm logged as $user and want to create/add a new trip
        $this->actingAs($user, 'api');
        $response = $this->json(
            'POST',
            route('trips.store'), [
                'date' => now()->format('m/d/Y'),
                'car_id' => $car->id,
                'miles' => 100.5,
            ]
        );

        // Then I should receive new trip data and it should be in saved in database
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('trips', [
            'miles' => 100.5,
            'miles_balance' => 100.5,
            'car_id' => $car->id,
            'user_id' => $user->id,
        ]);
    }

    public function storeTripProvider()
    {
        return [
            'empty values' => [
                [],
                [
                    'date' => 'The date field is required.',
                    'car_id' => 'The car id field is required.',
                    'miles' => 'The miles field is required.',
                ],
            ],
            'invalid date format' => [
                [
                    'date' => 'date',
                    'car_id' => 1,
                    'miles' => 100,
                ],
                [
                    'date' => 'The date is not a valid date.',
                ],
            ],
            'miles is a nagative value' => [
                [
                    'date' => '02/23/2022',
                    'car_id' => 1,
                    'miles' => -10,
                ],
                [
                    'miles' => 'The miles must be greater than 0.',
                ],
            ],
            'miles is a zero value' => [
                [
                    'date' => '02/23/2022',
                    'car_id' => 1,
                    'miles' => 0,
                ],
                [
                    'miles' => 'The miles must be greater than 0.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider storeTripProvider
     */
    public function test_logged_user_cannot_create_new_car_when_data_is_invalid($data, $errors)
    {
        // Given registered user with a car
        $user = factory(User::class)->create();
        $car = factory(Car::class)->create(['user_id' => $user->id]);

        // When I'm logged as $user and want to create/add a trip
        $this->actingAs($user, 'api');
        $response = $this->json(
            'POST',
            route('trips.store'),
            $data,
        );

        // Then I should receive validation errors and trip shouldn't be created
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors($errors);

        $this->assertDatabaseCount('trips', 0);
    }

    public function test_logged_user_can_create_new_trip_when_data_is_valid_and_new_trip_is_between_oldest_and_newest()
    {
        // Given registered user
        $user = factory(User::class)->create();
        $car = factory(Car::class)->create([
            'user_id' => $user->id,
        ]);
        $newestTrip = factory(Trip::class)->create([
            'car_id' => $car->id, 'miles' => '10.5', 'took_place_at' => '2022-02-23', 'user_id' => $user->id,
        ]);
        $newerTrip = factory(Trip::class)->create([
            'car_id' => $car->id, 'miles' => '25', 'took_place_at' => '2021-12-24', 'user_id' => $user->id,
        ]);
        $oldestTrip = factory(Trip::class)->create([
            'car_id' => $car->id, 'miles' => '30', 'took_place_at' => '2021-12-06', 'user_id' => $user->id,
        ]);

        // When I'm logged as $user and want to create a new trip which is not the newest
        $this->actingAs($user, 'api');
        $response = $this->json(
            'POST',
            route('trips.store'), [
                'date' => '12/20/2021',
                'car_id' => $car->id,
                'miles' => 100,
            ]
        );

        // Then new trip should be stored in database and newer trips (newestTrip, newerTrip) should have `miles_balance` updated
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('trips', [
            'miles' => 100,
            'miles_balance' => 130,
            'car_id' => $car->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('trips', [
            'id' => $newerTrip->id,
            'miles' => $newerTrip->miles,
            'miles_balance' => 155,
            'car_id' => $car->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('trips', [
            'id' => $newestTrip->id,
            'miles' => $newestTrip->miles,
            'miles_balance' => 165.5,
            'car_id' => $car->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('trips', [
            'id' => $oldestTrip->id,
            'miles' => $oldestTrip->miles,
            'miles_balance' => $oldestTrip->miles_balance,
            'car_id' => $car->id,
            'user_id' => $user->id,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Car;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;

class CarTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_authorized_users_can_access_cars()
    {
        // When I want to access my cars being unauthorized/not logged in
        $response = $this->json('GET', route('cars.index'));

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
        $response = $this->json('GET', route('cars.index'));

        // Then I should get only my cars
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(2, 'data');

        $data = collect($response['data']);
        $this->assertEquals($usersCars->pluck('id'), $data->pluck('id'));
    }

    public function test_only_authorized_users_can_create_cars()
    {
        // When I want to create a new car being unauthorized/not logged in
        $response = $this->json('POST', route('cars.store'), []);

        // Then I should get 401 response - Unauthorized
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logged_user_can_create_new_car_when_data_is_valid()
    {
        // Given registered user
        $user = factory(User::class)->create();
        $car = factory(Car::class)->make();

        // When I'm logged as $user and want to create/add my car
        $this->actingAs($user, 'api');
        $response = $this->json(
            'POST',
            route('cars.store'),
            $car->toArray(),
        );

        // Then I should receive new car data and it should be in saved in database
        $response->assertStatus(Response::HTTP_CREATED);

        $car = $user->cars()->first();
        $response->assertJsonFragment([
            'data' => Arr::only($car->toArray(), ['id', 'make', 'model', 'year'])
        ]);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'user_id' => $user->id,
            'make' => $car->make,
            'model' => $car->model,
            'year' => $car->year,
        ]);
    }

    public function storeCarProvider()
    {
        return [
            'empty values' => [
                [],
                [
                    'make' => 'The make field is required.',
                    'model' => 'The model field is required.',
                    'year' => 'The year field is required.',
                ],
            ],
            'too long strings, invalid types' => [
                [
                    'make' => Str::random(70),
                    'model' => Str::random(70),
                    'year' => Str::random(70),
                ],
                [
                    'make' => 'The make may not be greater than 64 characters.',
                    'model' => 'The model may not be greater than 64 characters.',
                    'year' => 'The year must be an integer.',
                ],
            ],
            'invalid year of production' => [
                [
                    'make' => 'KIA',
                    'model' => 'Stinger',
                    'year' => 1800,
                ],
                [
                    'year' => 'The year must be between 1900 and 2022.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider storeCarProvider
     */
    public function test_logged_user_cannot_create_new_car_when_data_is_invalid($data, $errors)
    {
        // Given registered user
        $user = factory(User::class)->create();

        // When I'm logged as $user and want to create/add my car
        $this->actingAs($user, 'api');
        $response = $this->json(
            'POST',
            route('cars.store'),
            $data,
        );

        // Then I should receive validation errors and car shouldn't be created
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors($errors);

        $this->assertDatabaseCount('cars', 0);
    }

    public function test_only_authorized_users_can_view_car()
    {
        // When I want to view a car being unauthorized/not logged in
        $response = $this->json('GET', route('cars.show', ['car' => 1]));

        // Then I should get 401 response - Unauthorized
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logged_user_cannot_view_other_users_car()
    {
        // Given 2 registered users
        $user = factory(User::class)->create();

        $otherUser = factory(User::class)->create();
        $car = factory(Car::class)->create([
            'user_id' => $otherUser->id,
        ]);

        // When I'm logged as $user and want to view $otherUser's car details
        $this->actingAs($user, 'api');
        $response = $this->json(
            'GET',
            route('cars.show', ['car' => $car->id]),
        );

        // Then I should get forbidden error and data shouldn't be presented
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    // TODO
//    public function test_logged_user_can_view_its_car()
//    {
//        // Given registered user
//        $user = factory(User::class)->create();
//        $car = factory(Car::class)->create([
//            'user_id' => $user->id,
//        ]);
//
//        // When I'm logged as $user and want to view my car details
//        $this->actingAs($user, 'api');
//        $response = $this->json(
//            'GET',
//            route('cars.show', ['car' => $car->id]),
//        );
//
//        // Then I should get my car details with trips
//        $response->assertStatus(Response::HTTP_OK);
//
//        $response->assertJsonFragment([
//            'data' => Arr::only($car->toArray(), ['id', 'make', 'model', 'year'])
//        ]);
//    }

    public function test_only_authorized_users_can_delete_cars()
    {
        // When I want to delete a car being unauthorized/not logged in
        $response = $this->json('GET', route('cars.delete', ['car' => 1]));

        // Then I should get 401 response - Unauthorized
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_logged_user_cannot_delete_other_users_car()
    {
        // Given 2 registered users
        $user = factory(User::class)->create();

        $otherUser = factory(User::class)->create();
        $car = factory(Car::class)->create([
            'user_id' => $otherUser->id,
        ]);

        // When I'm logged as $user and want to delete $otherUser's car
        $this->actingAs($user, 'api');
        $response = $this->json(
            'DELETE',
            route('cars.delete', ['car' => $car->id]),
        );

        // Then I should get forbidden error and car shouldn't be deleted
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('cars', ['id' => $car->id]);
    }

    public function test_logged_user_can_delete_its_car()
    {
        // Given 2 registered users
        $user = factory(User::class)->create();
        $car = factory(Car::class)->create([
            'user_id' => $user->id,
        ]);

        // When I'm logged as $user and want to delete my car
        $this->actingAs($user, 'api');
        $response = $this->json(
            'DELETE',
            route('cars.delete', ['car' => $car->id]),
        );

        // Then car should be deleted
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }
}

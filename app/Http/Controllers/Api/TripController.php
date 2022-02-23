<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Resources\TripWithCar as TripWithCarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $trips = $request->user()
            ->trips()
            ->with('car')
            ->orderBy('took_place_at', 'desc')
            ->get();

        return TripWithCarResource::collection($trips);
    }

    public function store(StoreTripRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $car = $user->cars()->findOrFail($data['car_id']);

        $trip = $user->trips()->create([
            'took_place_at' => $data['date'],
            'miles' => $data['miles'],
            'car_id' => $car->id,
        ]);

        return (new TripWithCarResource($trip))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

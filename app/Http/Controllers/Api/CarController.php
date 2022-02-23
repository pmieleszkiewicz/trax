<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Car;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarRequest;
use App\Http\Resources\Car as CarResource;
use App\Http\Resources\CarWithTrips;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $cars = $request->user()->cars;

        return CarResource::collection($cars);
    }

    public function show(Request $request, Car $car)
    {
        $user = $request->user();

        if ($user->cannot('view', $car)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return new CarWithTrips($car);
    }

    public function store(StoreCarRequest $request)
    {
        $car = $request->user()->cars()->create(
            $request->validated()
        );

        return (new CarResource($car))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

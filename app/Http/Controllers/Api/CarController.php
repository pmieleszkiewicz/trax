<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarRequest;
use App\Http\Resources\Car as CarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $cars = $request->user()->cars;

        return CarResource::collection($cars);
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

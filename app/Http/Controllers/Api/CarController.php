<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Car;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteCarRequest;
use App\Http\Requests\ShowCarRequest;
use App\Http\Requests\StoreCarRequest;
use App\Http\Resources\Car as CarResource;
use App\Http\Resources\CarWithTrips;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CarController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $db;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function index(Request $request)
    {
        $cars = $request->user()->cars;

        return CarResource::collection($cars);
    }

    public function show(ShowCarRequest $request, Car $car)
    {
        $car->loadCount('trips');

        return new CarWithTrips($car);
    }

    public function store(StoreCarRequest $request)
    {
        $this->db->beginTransaction();

        $car = $request->user()->cars()->create(
            $request->validated(),
        );

        $this->db->commit();

        return (new CarResource($car))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function delete(DeleteCarRequest $request, Car $car)
    {
        $this->db->beginTransaction();

        $car->delete();

        $this->db->commit();

        return response()->json('', Response::HTTP_NO_CONTENT);
    }
}

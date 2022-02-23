<?php

declare(strict_types=1);

namespace App\Observers;

use App\Car;
use App\Services\TripService;
use App\Trip;

class CarObserver
{
    /**
     * @var TripService
     */
    private $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function deleting(Car $car)
    {
        $car->trips->each(function (Trip $trip) {
            $this->tripService->decreaseMilesBalanceForNewerThan($trip, $trip->miles);
        });
    }
}

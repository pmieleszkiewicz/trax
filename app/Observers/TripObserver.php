<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\TripService;
use App\Trip;

class TripObserver
{
    /**
     * @var TripService
     */
    private $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function created(Trip $trip)
    {
        $this->tripService->updateMilesBalanceFor($trip);
        $this->tripService->increaseMilesBalanceForNewerThan($trip, $trip->miles);
    }
}

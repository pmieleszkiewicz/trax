<?php

declare(strict_types=1);

namespace App\Services;

use App\Trip;

class TripService
{
    /**
     * Calculates `miles_balance` for passed trip based on previous trips.
     *
     * @param Trip $trip
     */
    public function updateMilesBalanceFor(Trip $trip): void
    {
        $closestLastTrip = $trip->user
            ->trips()
            ->where('id', '!=', $trip->id)
            ->whereDate('took_place_at', '<=', $trip->took_place_at)
            ->latest('took_place_at')
            ->first();

        $closestLastTripBalance = (float) ($closestLastTrip->miles_balance ?? 0);
        $trip->miles_balance = $closestLastTripBalance + $trip->miles;

        $trip->save();
    }

    /**
     * Increases trip's `miles_balance` by difference value.
     * Used to update newer trips after creating a new one.
     *
     * @param Trip $trip
     * @param float $difference
     */
    public function increaseMilesBalanceForNewerThan(Trip $trip, float $difference): void
    {
        $trip->user
            ->trips()
            ->where('id', '!=', $trip->id)
            ->whereDate('took_place_at', '>', $trip->took_place_at)
            ->latest('took_place_at')
            ->get()
            ->each(function (Trip $trip) use ($difference) {
                $trip->miles_balance = $trip->miles_balance + $difference;
                $trip->save();
            });
    }

    /**
     * Decreases trip's `miles_balance` by difference value.
     * Used to update newer trips after deleting one.
     *
     * @param Trip $trip
     * @param float $difference
     */
    public function decreaseMilesBalanceForNewerThan(Trip $trip, float $difference): void
    {
        $trip->user
            ->trips()
            ->where('id', '!=', $trip->id)
            ->whereDate('took_place_at', '>', $trip->took_place_at)
            ->latest('took_place_at')
            ->get()
            ->each(function (Trip $trip) use ($difference) {
                $trip->miles_balance = $trip->miles_balance - $difference;
                dump($trip->miles_balance, $difference);
                $trip->save();
            });
    }
}

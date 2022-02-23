<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Car as CarResource;

class TripWithCar extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->took_place_at->format('m/d/Y'),
            'miles' => $this->miles,
            'total' => $this->miles_balance,
            'car' => new CarResource($this->car),
        ];
    }
}

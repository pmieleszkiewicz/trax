<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripWithCar as TripWithCarResource;
use Illuminate\Http\Request;

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
}

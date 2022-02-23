<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    protected $fillable = [
        'took_place_at',
        'user_id',
        'car_id',
        'miles',
        'miles_balance',
    ];

    protected $casts = [
        'miles' => 'float',
        'miles_balance' => 'float',
    ];

    protected $dates = [
        'took_place_at',
    ];

    /**
     * Returns user who took a trip
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns car that was used during a trip
     *
     * @return BelongsTo
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}

<?php

namespace App\Providers;

use App\Car;
use App\Observers\CarObserver;
use App\Observers\TripObserver;
use App\Trip;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Trip::observe(TripObserver::class);
        Car::observe(CarObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

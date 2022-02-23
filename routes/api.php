<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->group(function () {
    // User endpoints
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cars endpoints
    Route::prefix('cars')->as('cars.')->group(function () {
        Route::get('', 'CarController@index')->name('index');
        Route::post('', 'CarController@store')->name('store');
        Route::get('{car}', 'CarController@show')->name('show');
        Route::delete('{car}', 'CarController@delete')->name('delete');
    });

    // Trips endpoints
    Route::prefix('trips')->as('trips.')->group(function () {
        Route::get('', 'TripController@index')->name('index');
        Route::post('', 'TripController@store')->name('store');
    });
});

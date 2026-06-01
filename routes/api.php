<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingApiController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\MeApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Public
    Route::get('/listings', [ListingApiController::class, 'index']);
    Route::get('/listings/{idOrCode}', [ListingApiController::class, 'show']);

    // Auth public endpoints
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    // Leads: rate-limit 3/5min
    Route::post('/leads', [LeadApiController::class, 'store'])->middleware('throttle:3,5');

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::post('/listings', [ListingApiController::class, 'store']);
        Route::put('/listings/{id}', [ListingApiController::class, 'update']);
        Route::delete('/listings/{id}', [ListingApiController::class, 'destroy']);

        Route::get('/me', [MeApiController::class, 'show']);
        Route::get('/me/listings', [MeApiController::class, 'myListings']);
        Route::get('/me/stats', [MeApiController::class, 'myStats']);
    });
});

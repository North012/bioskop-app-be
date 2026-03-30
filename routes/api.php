<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FilmController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\StudioController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TheaterController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\AuthController;

Route::post('/login/user', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'error']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/auth/user', [UserController::class, 'authUser']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/store', [UserController::class, 'store']);
        Route::post('/update/{id}', [UserController::class, 'update']);
        Route::delete('/destroy/{id}', [UserController::class, 'destroy']);
    });
    
    Route::prefix('booking')->group(function() {
        Route::post('/store', [BookingController::class, 'store']);
        Route::get('active-ticket', [CustomerController::class, 'activeTicket']);
        Route::get('non-active-ticket', [CustomerController::class, 'nonActiveTicket']);
    });
});

Route::prefix('film')->group(function() {
    Route::get('/now-playing', [FilmController::class, 'nowPlaying']);
    Route::get('/film-schedule/{id}', [FilmController::class, 'filmScheduleList']);
    Route::get('/', [FilmController::class, 'index']);
    Route::get('/{id}', [FilmController::class, 'show']);
    Route::post('/store', [FilmController::class, 'store']);
    Route::post('/update/{id}', [FilmController::class, 'update']);
    Route::post('/updateStatus/{id}', [FilmController::class, 'updateStatus']);
    Route::delete('destroy/{id}', [FilmController::class, 'destroy']);
    
});

Route::prefix('location')->group(function() {
    Route::get('/', [LocationController::class, 'index']);
    Route::get('/{id}', [LocationController::class, 'show']);
    Route::post('/store', [LocationController::class, 'store']);
    Route::post('/update/{id}', [LocationController::class, 'update']);
    Route::delete('/destroy/{id}', [LocationController::class, 'destroy']);
});

Route::prefix('studio')->group(function() {
    Route::get('/', [StudioController::class, 'index']);
    Route::get('/{id}', [StudioController::class, 'show']);
    Route::post('/store', [StudioController::class, 'store']);
    Route::post('/update/{id}', [StudioController::class, 'update']);
    Route::delete('/destroy/{id}', [StudioController::class, 'destroy']);
});

Route::prefix('schedule')->group(function() {
    Route::get('/', [ScheduleController::class, 'index']);
    Route::get('/{id}', [ScheduleController::class, 'show']);
    Route::get('/show/{id}', [ScheduleController::class, 'showEdit']);
    Route::post('/store', [ScheduleController::class, 'store']);
    Route::post('/update/{id}', [ScheduleController::class, 'update']);
    Route::delete('/destroy/{id}', [ScheduleController::class, 'destroy']);
});

Route::prefix('seat')->group(function() {
    Route::get('/', [SeatController::class, 'index']);
    Route::get('/{id}', [SeatController::class, 'show']);
    Route::post('/store', [SeatController::class, 'store']);
    Route::post('/update', [SeatController::class, 'update']);
    Route::delete('/destroy/{id}', [SeatController::class, 'destroy']);
});

Route::prefix('theater')->group(function() {
    Route::get('/', [TheaterController::class, 'index']);
    Route::get('/{id}', [TheaterController::class, 'show']);
    Route::post('/store', [TheaterController::class, 'store']);
    Route::post('/update/{id}', [TheaterController::class, 'update']);
    Route::delete('/destroy/{id}', [TheaterController::class, 'destroy']);
});



Route::get('booking/seat-detail/{id}', [BookingController::class, 'detailPickOrder']);
Route::post('booking/seat-detail', [BookingController::class, 'updateSeatDetail']);

Route::get('booking/payment-detail/{schedule_id}', [BookingController::class, 'paymentDetail']);

Route::get('booking/booking-detail/{id}', [CustomerController::class, 'detailTransaction']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

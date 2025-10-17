<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ServiceController::class,'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class,'show'])->name('services.show');
Route::post('/bookings', [BookingController::class,'store'])->name('bookings.store');


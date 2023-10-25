<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\LineBotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleCalendarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/line/webhook', [LineBotController::class, 'webhook']);

Route::resource('/bookings', BookingController::class);
//Route::post('/bookings', [BookingController::class, 'store']);

Route::get('/auth/google', [GoogleCalendarController::class, 'auth']);
Route::get('/auth/google/callback', [GoogleCalendarController::class, 'authCallback'])->name('auth.google.callback');



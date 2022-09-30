<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('register', [AuthController::class, 'register'])->name('api.user.register');
Route::post('login', [AuthController::class, 'login'])->name('api.user.login');
Route::post('resend-otp', [AuthController::class, 'resendOTP'])->name('api.resendOTP');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('api.user.verifyOtp');
Route::post('states', [AuthController::class, 'states'])->name('api.states');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.user.logout');

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::post('add_merchant', [AuthController::class, 'add_merchant'])->name('api.user.add_merchant');
    });

    // Admin routes
    Route::prefix('investor')->group(function () {
        Route::post('setup', [AuthController::class, 'setup'])->name('api.user.setup');
    });
});

<?php

use App\Models\ListMasterLogin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TrAttendanceController;
use App\Http\Controllers\ListMasterLoginController;
use App\Http\Controllers\LoginControllerProduction;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', function () {
    return view('welcome');
});


Route::get("/login", [ListMasterLoginController::class, 'index']);

Route::post('/test', [ListMasterLoginController::class, 'login'])->withoutMiddleware(VerifyCsrfToken::class);

Route::post("/login", [LoginControllerProduction::class, 'login'])->withoutMiddleware(VerifyCsrfToken::class);

Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->withoutMiddleware(VerifyCsrfToken::class);
Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->withoutMiddleware(VerifyCsrfToken::class);
Route::get('api/attendance/status', [AttendanceController::class, 'checkStatus'])->withoutMiddleware(VerifyCsrfToken::class);
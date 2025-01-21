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




Route::post('/test', [ListMasterLoginController::class, 'login'])->withoutMiddleware(VerifyCsrfToken::class);


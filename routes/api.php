<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceProductionController;
use App\Http\Controllers\LoginControllerProduction;

Route::post("/login", [LoginControllerProduction::class, 'login']);
// Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
// Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
// Route::get('api/attendance/status', [AttendanceController::class, 'checkStatus']);

Route::post("/attendance/check-in-user", [AttendanceProductionController::class, 'checkIn']);
Route::post("/attendance/check-out-user", [AttendanceProductionController::class, "checkOut"]);
Route::get("/attendance/status", [AttendanceProductionController::class, "checkStatus"]);
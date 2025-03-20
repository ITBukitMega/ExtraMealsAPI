<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceNEATController;
use App\Http\Controllers\AttendanceProductionController;
use App\Http\Controllers\LoginAttendanceProduction;
use App\Http\Controllers\LoginControllerProduction;


//Routing untuk ExtraMealV3.1.1 
Route::post("/login", [LoginControllerProduction::class, 'login']);
Route::post("/change-password", [LoginControllerProduction::class, 'changePassword']);

// Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
// Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
// Route::get('api/attendance/status', [AttendanceController::class, 'checkStatus']);

Route::post("/attendance/check-in-user", [AttendanceProductionController::class, 'checkIn']);
Route::post("/attendance/check-out-user", [AttendanceProductionController::class, "checkOut"]);
Route::get("/attendance/status", [AttendanceProductionController::class, "checkStatus"]);

Route::post('/check-version', [LoginControllerProduction::class, 'checkVersion']);


// routing untuk NEAT Application
Route::get("/login/attendance", [LoginAttendanceProduction::class, 'index']);
Route::post("/login/attendance", [LoginAttendanceProduction::class, 'login']);
Route::post('/check-version/attendance', [LoginAttendanceProduction::class, 'checkVersion']);
Route::post("/change-password/attendance", [LoginAttendanceProduction::class, 'changePassword']);

Route::post("/attendance/check-in-user/NEAT", [AttendanceNEATController::class, 'checkIn']);
Route::post("/attendance/check-out-user/NEAT", [AttendanceNEATController::class, "checkOut"]);
Route::get("/attendance/status/NEAT", [AttendanceNEATController::class, "checkStatus"]);
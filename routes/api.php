<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SickLeaveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AttendanceNEATController;
use App\Http\Controllers\AttendanceV2Controller;
use App\Http\Controllers\LoginAttendanceProduction;
use App\Http\Controllers\LoginControllerProduction;
use App\Http\Controllers\AttendanceProductionController;

//Routing untuk ExtraMealV3.1.1 
Route::post("/login", [LoginControllerProduction::class, 'login']);
Route::post("/change-password", [LoginControllerProduction::class, 'changePassword']);
Route::post('/check-version', [LoginControllerProduction::class, 'checkVersion']);

Route::post("/attendance/check-in-user", [AttendanceProductionController::class, 'checkIn']);
Route::post("/attendance/check-out-user", [AttendanceProductionController::class, "checkOut"]);
Route::get("/attendance/status", [AttendanceProductionController::class, "checkStatus"]);

// routing untuk NEAT Application
Route::get("/login/attendance", [LoginAttendanceProduction::class, 'index']);
Route::post("/login/attendance", [LoginAttendanceProduction::class, 'login']);
Route::post('/check-version/attendance', [LoginAttendanceProduction::class, 'checkVersion']);
Route::post("/change-password/attendance", [LoginAttendanceProduction::class, 'changePassword']);

// Original attendance V1 routes (kept for backward compatibility)
Route::post("/attendance/check-in-user/NEAT", [AttendanceNEATController::class, 'checkIn']);
Route::post("/attendance/check-out-user/NEAT", [AttendanceNEATController::class, "checkOut"]);
Route::get("/attendance/status/NEAT", [AttendanceNEATController::class, "checkStatus"]);

// New attendance V2 routes for multiple check-ins/outs
Route::post("/attendance/record/NEAT", [AttendanceV2Controller::class, 'recordAttendance']);
Route::get("/attendance/log/NEAT", [AttendanceV2Controller::class, 'getAttendanceLog']);

// Add this route to your routes/api.php file

Route::get('/inbox/NEAT', 'App\Http\Controllers\InboxController@getInbox');

Route::post("/leave-request/submit/NEAT", [LeaveRequestController::class, 'submitLeaveRequest']);
Route::post("/sick-leave/submit/NEAT", [SickLeaveController::class, 'submitSickLeave']);
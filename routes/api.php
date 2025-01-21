<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginControllerProduction;

Route::post("/login", [LoginControllerProduction::class, 'login']);
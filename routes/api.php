<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



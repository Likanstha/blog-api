<?php

use App\Http\Controllers\AuthController;


// Authentication Routes
Route::get('/register', [AuthController::class, 'register']);



<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;


// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Post Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'create']);
    Route::patch('/posts/{id}', [PostController::class, 'update']);
    Route::get('/posts/{id}', [PostController::class, 'showPost']);
    Route::get('/posts', [PostController::class, 'showAllPosts']);
    Route::delete('/posts/{id}', [PostController::class, 'delete']);
});

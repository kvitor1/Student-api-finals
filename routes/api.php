<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes via Sanctum Token
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Public / Read-Only for logged-in users
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);

    // Admin-Only Routes for Create, Update, Delete
    Route::post('/students', [StudentController::class, 'store']);
    Route::put('/students/{id}', [StudentController::class, 'update']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
});
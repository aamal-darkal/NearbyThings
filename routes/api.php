<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// ===== الـ API (Laravel Sanctum) =====

// تسجيل الدخول: بيرجع توكن
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// بتحتاج توكن: Authorization: Bearer <token>
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

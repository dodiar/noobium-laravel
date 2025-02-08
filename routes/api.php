<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('sign-up', [AuthController::class, 'signUp']);
Route::post('sign-in', [AuthController::class, 'signIn']);

Route::middleware('auth:api')->group(function() {
    Route::middleware(['auth:api', 'jwt.refresh'])->post('refresh', [AuthController::class, 'refresh']);
    Route::post('sign-out', [AuthController::class, 'signOut']);
});

Route::middleware('jwt.refresh')->post('refresh', [AuthController::class, 'refresh']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

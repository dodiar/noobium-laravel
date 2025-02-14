<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Me\ProfileController;
use App\Http\Controllers\Me\ArticleController as MeArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\GoogleAuthController;

Route::post('sign-up', [AuthController::class, 'signUp']);
Route::post('sign-in', [AuthController::class, 'signIn']);

Route::post('sign-in/google', [GoogleAuthController::class, 'signIn']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{categorySlug}', [CategoryController::class, 'show']);

Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{slug}', [ArticleController::class, 'show']);

Route::middleware('auth:api')->group(function() {
    Route::prefix('me')->group(function() {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);

        Route::apiResource('articles', MeArticleController::class);
    });
    Route::post('sign-out', [AuthController::class, 'signOut']);
});

Route::middleware('jwt.refresh')->post('refresh', [AuthController::class, 'refresh']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

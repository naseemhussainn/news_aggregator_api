<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Article Routes
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::get('/feed', [ArticleController::class, 'personalizedFeed']);

    // User Preferences
    Route::get('/preferences', [UserPreferenceController::class, 'index']);
    Route::post('/preferences/sources', [UserPreferenceController::class, 'setSources']);
    Route::post('/preferences/categories', [UserPreferenceController::class, 'setCategories']);
    Route::post('/preferences/authors', [UserPreferenceController::class, 'setAuthors']);

});

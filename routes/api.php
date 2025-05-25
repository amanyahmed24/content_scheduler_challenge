<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    //auth
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);


    //posts
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::get('/', [PostController::class,  'index']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class,  'destroy']);
    });

    //platforms
    Route::prefix('platforms')->group(function () {
        Route::get('/', [PlatformController::class, 'index']);
    });
});

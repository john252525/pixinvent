<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return $request->user()->getUserData();
})->middleware('auth:sanctum');

/** Authentication Routes... */
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'transactions', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [TransactionController::class, 'index']);
});

Route::group(['prefix' => 'sources', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [SourcesController::class, 'index']);
});


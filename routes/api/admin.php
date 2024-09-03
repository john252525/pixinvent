<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Facades\Route;

/** Admin Routes... */
Route::group(['prefix' => '/', 'middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::put('/', [UsersController::class, 'store']);
        Route::post('/{user}', [UsersController::class, 'update']);
        Route::delete('/{user}', [UsersController::class, 'destroy']);
    });
    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', [RolesController::class, 'index']);
        Route::put('/', [RolesController::class, 'store']);
        Route::post('/{role}', [RolesController::class, 'update']);
        Route::delete('/{role}', [RolesController::class, 'destroy']);
    });
    Route::group(['prefix' => 'permissions'], function () {
        Route::get('/', [PermissionsController::class, 'index']);
        Route::put('/', [PermissionsController::class, 'store']);
        Route::post('/{permission}', [PermissionsController::class, 'update']);
        Route::delete('/{permission}', [PermissionsController::class, 'destroy']);
    });
});

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\Payments\YookassaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'yookassa', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/', [YookassaController::class, 'create']);
});

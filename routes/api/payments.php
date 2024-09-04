<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\Payments\UkassaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('yookassa')->group(function () {
    Route::post('/', [UkassaController::class, 'create']);
});

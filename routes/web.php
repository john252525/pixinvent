<?php

use Illuminate\Support\Facades\Route;

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');

Route::get('auth/password/reset', [\App\Http\Controllers\Api\User\AuthController::class, 'passwordReset'])->name('password.reset');

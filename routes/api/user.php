<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/', function (Request $request) {
    return $request->user()->getUserData();
});

/** Authentication Routes... */
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
    Route::post('set-new-password', 'resetPassword')->name('password.update');

    Route::post('verify-email', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => __('Verification link sent!')]);
    })->middleware('auth:sanctum', 'throttle:6,1')->name('verification.send');

    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::group(['prefix' => 'transactions', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [TransactionController::class, 'index']);
});

Route::group(['prefix' => 'sources', 'middleware' => 'auth:sanctum'], function () {
    Route::post('add', [SourcesController::class, 'addAccount']);
    Route::get('{source}', [SourcesController::class, 'index']);
    Route::put('{source}', [SourcesController::class, 'store']);
    Route::post('{source}', [SourcesController::class, 'update']);
    Route::delete('{source}', [SourcesController::class, 'destroy']);
    Route::post('{source}/get-qr-code', [\App\Http\Controllers\Api\User\SourcesController::class, 'getInfo']);
    Route::post('{source}/get-qr', [\App\Http\Controllers\Api\User\SourcesController::class, 'getQR']);
    Route::post('{source}/get-info-by-token', [\App\Http\Controllers\Api\User\SourcesController::class, 'getInfoByToken']);
    Route::post('{source}/get-info', [\App\Http\Controllers\Api\User\SourcesController::class, 'getInfo']);
    Route::post('{source}/send-two-factor-auth', [\App\Http\Controllers\Api\User\SourcesController::class, 'twoFactorAuth']);
    Route::post('{source}/send-telegram-code', [\App\Http\Controllers\Api\User\SourcesController::class, 'sendTelegramCode']);
    Route::post('{source}/solve-challenge', [\App\Http\Controllers\Api\User\SourcesController::class, 'solveChallenge']);
    Route::post('{source}/switch-state', [\App\Http\Controllers\Api\User\SourcesController::class, 'switchState']);
    Route::post('{source}/set-state', [\App\Http\Controllers\Api\User\SourcesController::class, 'setStateAction']);
    Route::post('{source}/force-stop', [\App\Http\Controllers\Api\User\SourcesController::class, 'forceStopAction']);
    Route::post('{source}/switch-auth', [\App\Http\Controllers\Api\User\SourcesController::class, 'switchAuth']);
    Route::post('{source}/get-auth-code', [\App\Http\Controllers\Api\User\SourcesController::class, 'getInfo']);
    Route::post('{source}/clear-session', [\App\Http\Controllers\Api\User\SourcesController::class, 'clearSessionAction']);
});

Route::group(['prefix' => 'mailing', 'middleware' => 'auth:sanctum', 'controller' => MailingController::class], function () {
    Route::get('get', 'index')->name('get');
    Route::get('get-messages/{id}', 'getMessages');
    Route::get('edit-status/{id}/{state_id}', 'editStatus');
    Route::get('get-user/{user}', 'getUser');
    Route::delete('delete/{id}', 'destroy')->name('delete');

    Route::post('create', 'store');
    Route::post('edit', 'edit');
});

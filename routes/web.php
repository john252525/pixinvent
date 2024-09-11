<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::any('webhooks/{service}/{type?}', function (Request $request, string $service, ?string $type = null) {
    try {
        \App\Models\Webhook::create(['service' => $service, 'type' => $type, 'payload' => $request->all()]);
        return response()->json('ok');
    } catch (\Exception $e) {
        \Log::error('Webhook error: '.$service.' Message: '.$e->getMessage().' Payload: '.json_encode($request->all()));
        return response()->json('ok');
    }
});

Route::get('auth/password/reset', [\App\Http\Controllers\Api\User\AuthController::class, 'passwordReset'])->name('password.reset');

Route::get('{any?}', function () {
    return view('application');
})->where('any', '.*');


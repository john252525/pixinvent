<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

foreach (\File::allFiles(__DIR__.'/api') as $file) {
    $fileName = $file->getFilename();
    $prefix = basename($fileName, '.php');

    Route::prefix("$prefix")
        ->middleware('api')
        ->group(__DIR__."/api/{$fileName}");
}

Route::put('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'ru'])) {
        abort(400);
    }

    \App::setLocale($locale);

    return response()->noContent(201);
});

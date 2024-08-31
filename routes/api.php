<?php

use Illuminate\Support\Facades\Route;

foreach (\File::allFiles(__DIR__.'/api') as $file) {
    $fileName = $file->getFilename();
    $prefix = basename($fileName, '.php');
    Route::prefix("$prefix")->group(__DIR__."/api/{$fileName}");
}

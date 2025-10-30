<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('file/{file}', function ($file) {
    $path = storage_path('app/private/' . $file);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('file', '.*');

<?php

use Illuminate\Support\Facades\Route;

// Все URL → Vue-приложение (SPA)
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
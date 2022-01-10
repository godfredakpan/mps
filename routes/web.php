<?php

use Illuminate\Support\Facades\Route;

Route::get('/redis', function () {
    dd(extension_loaded('redis'));
});
Route::view('/offline', 'offline');
Route::view('/welcome', 'welcome');

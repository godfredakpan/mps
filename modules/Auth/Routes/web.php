<?php

use Illuminate\Support\Facades\Route;

// Auth
// Auth::routes();
Route::post('/login', 'LoginController@login');
Route::get('/logout', 'LoginController@logout');
Route::get('/login', 'LoginController@login')->name('login');
Route::post('/password/reset', 'ResetPasswordController@reset');
Route::delete('/logout', 'LoginController@logout')->name('logout');
Route::get('/password/email', fn () => redirect()->to('/auth/forgot'));
Route::post('/password/email', 'ForgotPasswordController@sendResetLinkEmail');
Route::get('/password/reset/{token}', fn ($token) => redirect()->to('/admin#/login?reset=' . $token))->name('password.reset');

Route::prefix('auth')->group(function () {
    Route::delete('logout', 'LoginController@logout')->name('auth.logout');
    Route::post('login', 'LoginController@login')->name('auth.login.post');
    Route::view('login', 'auth::login')->middleware(['guest'])->name('auth.login');
    Route::view('forgot', 'auth::forgot')->middleware(['guest'])->name('auth.password.forgot');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('auth.password.reset.post');
    Route::view('reset-password/{token}', 'auth::reset')->middleware(['guest'])->name('auth.password.reset');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('auth.password.email');

    Route::view('/', 'auth::index');
    Route::view('{any}', 'auth::index')->where('any', '.*');
});

Route::prefix('modules')->group(function () {
    Route::get('/', 'ModuleController@index');
    Route::post('/enable', 'ModuleController@enable');
    Route::post('/disable', 'ModuleController@disable');
    Route::post('/install', 'ModuleController@install');
});

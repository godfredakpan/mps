<?php
use Illuminate\Support\Facades\Route;
use Tecdiary\Installer\Http\Middleware\CanInstall;
use Tecdiary\Installer\Http\Controllers\InstallController;

Route::prefix('install')->middleware('web')->middleware('\\' . CanInstall::class)->group(function () {
    Route::get('/', ['\\' . InstallController::class, 'index']);
    Route::post('demo', ['\\' . InstallController::class, 'demo']);
    Route::post('save', ['\\' . InstallController::class, 'save']);
    Route::get('checklist', ['\\' . InstallController::class, 'show']);
    Route::post('account', ['\\' . InstallController::class, 'account']);
    Route::post('license', ['\\' . InstallController::class, 'license']);
    Route::post('finalize', ['\\' . InstallController::class, 'finalize']);
});

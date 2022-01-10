<?php

namespace Tecdiary\Installer;

use Illuminate\Support\ServiceProvider;
use Tecdiary\Installer\Commands\ResetData;
use Tecdiary\Installer\Commands\UpdateMPS;
use Tecdiary\Installer\Commands\InstallModule;
use Tecdiary\Installer\Commands\ComposerUpdate;

class InstallServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // if ($this->app->runningInConsole()) { }
        $this->commands([
            ResetData::class,
            UpdateMPS::class,
            InstallModule::class,
            ComposerUpdate::class,
        ]);
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'installer');
        //php artisan vendor:publish --tag=views --force
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/installer'),
        ], 'views');
        //php artisan vendor:publish --tag=assets --force
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/installer'),
        ], 'assets');
    }
}

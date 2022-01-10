<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerViews();
        $this->registerConfig();
        $this->registerFactories();
        $this->registerTranslations();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $modules = [];
        foreach (app('modules')->collections() as $name => $value) {
            $modules[] = module_data(mb_strtolower($name));
        }
        View::composer('*', function ($view) use ($modules) {
            $view->with('modules', $modules);
        });
    }

    public function provides()
    {
        return [];
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    public function registerFactories()
    {
        if (!app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/auth');
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'auth');
        } else {
            $this->loadJsonTranslationsFrom(__DIR__ . '/../Resources/lang');
            // $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'auth');
        }
    }

    public function registerViews()
    {
        $sourcePath = __DIR__ . '/../Resources/views';
        $viewPath   = resource_path('views/modules/auth');
        $this->publishes([$sourcePath => $viewPath], 'views');
        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/auth';
        }, Config::get('view.paths')), [$sourcePath]), 'auth');
    }

    protected function registerConfig()
    {
        $this->publishes([__DIR__ . '/../Config/config.php' => config_path('auth.php'), ], 'config');
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'auth');
    }
}

<?php

namespace Modules\MPS\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class MPSServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerViews();
        $this->registerConfig();
        $this->registerPolicies();
        $this->registerFactories();
        $this->registerObservers();
        $this->registerMiddlewares();
        $this->registerTranslations();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->commands([
            \Modules\MPS\Console\RemoveExpiredStock::class,
            \Modules\MPS\Console\CreateRecurringSales::class,
            \Modules\MPS\Console\SendPaymentReminders::class,
            \Modules\MPS\Console\ResetReferenceSequence::class,
            \Modules\MPS\Console\CalculateStaffSalaries::class,
            \Modules\MPS\Console\CreateRecurringExpenses::class,
        ]);
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
        if (demo() || env('APP_ENV') != 'production') {
            app(Factory::class)->load(__DIR__ . '/../Database/Factories');
        }
    }

    public function registerMiddlewares()
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('staff', \Modules\MPS\Http\Middleware\CheckForCustomer::class);
        $router->aliasMiddleware('impersonate', \Modules\MPS\Http\Middleware\Impersonate::class);
        $router->aliasMiddleware('location', \Modules\MPS\Http\Middleware\CheckForLocationId::class);
    }

    public function registerObservers()
    {
        \Modules\MPS\Models\Stock::observe(\Modules\MPS\Observers\StockObserver::class);
        \Modules\MPS\Models\Income::observe(\Modules\MPS\Observers\IncomeObserver::class);
        \Modules\MPS\Models\Salary::observe(\Modules\MPS\Observers\SalaryObserver::class);
        \Modules\MPS\Models\Expense::observe(\Modules\MPS\Observers\ExpenseObserver::class);
        \Modules\MPS\Models\Payment::observe(\Modules\MPS\Observers\PaymentObserver::class);
        \Modules\MPS\Models\GiftCard::observe(\Modules\MPS\Observers\GiftCardObserver::class);
        \Modules\MPS\Models\Variation::observe(\Modules\MPS\Observers\VariationObserver::class);
        \Modules\MPS\Models\AssetTransfer::observe(\Modules\MPS\Observers\AssetTransferObserver::class);
        \Modules\MPS\Models\VariationStock::observe(\Modules\MPS\Observers\VariationStockObserver::class);
        \Modules\MPS\Models\JournalTransaction::observe(\Modules\MPS\Observers\JournalTransactionObserver::class);
    }

    public function registerPolicies()
    {
        $this->app->register(AuthServiceProvider::class);
    }

    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/mps');

        if (is_dir($langPath)) {
            $this->loadJsonTranslationsFrom($langPath);
            $this->loadTranslationsFrom($langPath, 'mps');
        } else {
            $this->loadJsonTranslationsFrom(__DIR__ . '/../Resources/lang');
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'mps');
        }
    }

    public function registerViews()
    {
        $sourcePath = __DIR__ . '/../Resources/views';
        $viewPath   = resource_path('views/modules/mps');
        $this->publishes([$sourcePath => $viewPath], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/mps';
        }, Config::get('view.paths')), [$sourcePath]), 'mps');
    }

    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('mps.php'),
        ], 'config');
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'mps');
    }
}

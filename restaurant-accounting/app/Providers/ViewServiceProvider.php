<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share active currency with all views
        View::composer('*', function ($view) {
            $activeCurrency = getActiveCurrency();
            $allCurrencies = getAllActiveCurrencies();
            
            $view->with([
                'activeCurrency' => $activeCurrency,
                'allCurrencies' => $allCurrencies,
            ]);
        });
    }
}

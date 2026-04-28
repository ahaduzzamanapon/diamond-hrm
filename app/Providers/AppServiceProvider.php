<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->getHost() !== 'localhost' && request()->getHost() !== '127.0.0.1') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        \Illuminate\Pagination\Paginator::defaultView('pagination.custom');
    }
}

<?php

namespace App\Providers;

use App\Interfaces\ProductSearchInterface;
use App\Services\Decorators\SearchMonitoringDecorator;
use App\Services\SearchWithCacheService;
use App\Services\SearchWithOutCacheService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductSearchInterface::class, function ($app) {
            if (request()->query('use_cache') == 1) {
                $core = new SearchWithCacheService();
            } else {
                $core = new SearchWithOutCacheService();
            }

            return new SearchMonitoringDecorator($core);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('search_api', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}

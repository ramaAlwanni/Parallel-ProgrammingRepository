<?php

namespace App\Providers;

use App\Interfaces\OrderServiceInterface;
use App\Services\AsyncOrderDecorator;
use App\Services\PerformanceMonitorDecorator;
use App\Services\SafeOrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OrderServiceInterface::class, function () {
            return new AsyncOrderDecorator(
                new PerformanceMonitorDecorator(
                    new SafeOrderService()
                )
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

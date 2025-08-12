<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repositories
        $this->app->bind(
            \App\Repositories\OrderRepositoryInterface::class,
            \App\Repositories\EloquentOrderRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}

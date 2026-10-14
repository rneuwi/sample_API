<?php

namespace App\Providers;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\InMemoryEmployeeRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // A singleton so every consumer within one application instance
        // (controller, service, validation rule) shares the same collection.
        $this->app->singleton(EmployeeRepositoryInterface::class, InMemoryEmployeeRepository::class);

        // To persist employees between requests without a database, bind a
        // cache-backed implementation here instead (see "Data persistence" in
        // the README). Nothing else in the application needs to change:
        // $this->app->singleton(EmployeeRepositoryInterface::class, CacheEmployeeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

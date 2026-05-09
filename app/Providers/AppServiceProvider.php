<?php

namespace App\Providers;

use App\Models\LeaveType;
use App\Observers\LeaveTypeObserver;
use Illuminate\Pagination\Paginator;
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
        //
        Paginator::useBootstrapFive();

        // Register model observers
        LeaveType::observe(LeaveTypeObserver::class);
    }
}

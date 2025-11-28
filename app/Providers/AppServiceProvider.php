<?php

namespace App\Providers;

use App\Models\ContactLead;
use App\Observers\ContactLeadObserver;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register CurrentTenant as a singleton (request-scoped)
        $this->app->singleton(CurrentTenant::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ContactLead::observe(ContactLeadObserver::class);
    }
}

<?php

namespace App\Providers;

use App\Models\Purchase;
use App\Models\User;
use App\Policies\PurchasePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Purchase::class, PurchasePolicy::class);

        Gate::define('runMigrations', fn (User $user) => $user->canRunMigrations());
    }
}

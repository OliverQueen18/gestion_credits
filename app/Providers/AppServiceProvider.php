<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Gate::define('perform-financial-operations', function (User $user): bool {
            return $user->canPerformFinancialOperations();
        });

        Gate::define('manage-administration', function (User $user): bool {
            return $user->canManageAdministration();
        });
    }
}

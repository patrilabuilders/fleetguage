<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\SubAccount;
use App\Models\User;
use App\Observers\AssetObserver;
use App\Observers\AssetTypeObserver;
use App\Observers\ChargeableAccountObserver;
use App\Observers\SubAccountObserver;
use App\Observers\UserObserver;
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
        Paginator::useBootstrapFive();

        User::observe(UserObserver::class);
        Asset::observe(AssetObserver::class);
        AssetType::observe(AssetTypeObserver::class);
        ChargeableAccount::observe(ChargeableAccountObserver::class);
        SubAccount::observe(SubAccountObserver::class);
    }
}

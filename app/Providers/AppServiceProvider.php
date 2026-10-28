<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
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
        Schema::defaultStringLength(191);

        Gate::before(function ($user, $ability) {
            if ($user && ($user->type === 'company' || $user->type === 'superadmin')) {
                return true;
            }
        });

        if (!class_exists('Modules\LandingPage\Entities\MarketplacePageSetting')) {
            class_alias(\Workdo\LandingPage\Entities\MarketplacePageSetting::class, 'Modules\LandingPage\Entities\MarketplacePageSetting');
        }

        if (!class_exists('Workdo\ProductService\Http\Controllers\ProductServiceController') && class_exists('Workdo\ProductService\Http\Controllers\ProductServiceItemController')) {
            class_alias('Workdo\ProductService\Http\Controllers\ProductServiceItemController', 'Workdo\ProductService\Http\Controllers\ProductServiceController');
        }
    }
}

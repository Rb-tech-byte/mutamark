<?php

namespace Workdo\Fleet\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot()
    {
        view()->composer(['invoice.create','invoice.edit','invoice.index','invoice.grid'], function ($view){
            if (Auth::check() && module_is_active('Fleet')) {

                $view->getFactory()->startPush('account_type', view('fleet::invoice.account_type'));
            }
        });
    }

    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

<?php

namespace Modules\WhatsAppAPI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Modules\Reminder\Entities\Reminder;


class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

     public function boot(){
        view()->composer(['reminder::reminder.create','reminder::reminder.edit'], function ($view)
        {

            if (Auth::check() && module_is_active('WhatsAppAPI'))
            {
                $reminder = [];
                $route = \Request::route()->getName();
                $notification = [];
                if($route == 'reminder.edit'){
                    $ids = \Request::segment(2);
                    $reminder = Reminder::find($ids);
                    $notification = Explode(',' ,$reminder->action );
                }
                $view->getFactory()->startPush('action_name', view('whatsappapi::action_name',compact('reminder','notification')));
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

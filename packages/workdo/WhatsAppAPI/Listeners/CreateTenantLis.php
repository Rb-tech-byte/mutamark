<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\PropertyManagement\Events\CreateTenant;
use Modules\PropertyManagement\Entities\Property;
use App\Models\User;

class CreateTenantLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CreateTenant $event)
    {
        $tenant = $event->tenant;
        $property = Property::find($tenant->property_id);
        $user = User::find($tenant->user_id);

        if (module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Tenant')) && company_setting('Whatsappapi New Tenant')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name,
                    'property_name' => $property->name
                ];
                SendMsg::SendMsgs($user->mobile_no, $uArr , 'New Tenant');
            }
        }
    }
}

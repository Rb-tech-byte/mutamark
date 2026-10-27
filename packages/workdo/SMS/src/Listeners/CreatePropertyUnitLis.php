<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\PropertyManagement\Events\CreatePropertyUnit;
use Workdo\PropertyManagement\Entities\Property;
class CreatePropertyUnitLis
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
    public function handle(CreatePropertyUnit $event)
    {
        $propertyUnit = $event->propertyUnit;
        $property = Property::find($propertyUnit->property_id);
        $to = \Auth::user()->mobile_no;

        if (module_is_active('SMS') && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Property Units')) && company_setting('SMS New Property Units')  == true) {

            if(!empty($propertyUnit) && !empty($property) && !empty($to))
            {
                $uArr = [
                    'unit_name' => $propertyUnit->name,
                    'property_name' => $property->name
                ];
                SendMsg::SendMsgs($to, $uArr , 'New Property Units');
            }
        }
    }
}

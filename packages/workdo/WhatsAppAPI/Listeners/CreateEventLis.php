<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\Hrm\Events\CreateEvent;

class CreateEventLis
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
    public function handle(CreateEvent $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is')=='on' && !empty(company_setting('Whatsappapi New Event')) && company_setting('Whatsappapi New Event')  == true)
        {
            $request = $event->request;
            $event = $event->event;
            $branch = \Modules\Hrm\Entities\Branch::find($request->branch_id);
            $employee = \Modules\Hrm\Entities\Employee::whereIn('id', $request->employee_id)->get();

            if(empty($branch)){
                $branchs = \Modules\Hrm\Entities\Branch::where('workspace',getActiveWorkSpace())->where('created_by',$event->created_by)->get()->pluck('name');
                $branchs_detail = [];
                if (count($branchs) > 0) {
                    foreach ($branchs as $datasand) {
                        $branchs_detail[] = $datasand;
                    }
                }
            $branch = implode(',', $branchs_detail);
            }
            if(count($employee) == 0){
                $employee = \Modules\Hrm\Entities\Employee::where('workspace',getActiveWorkSpace())->where('created_by',$event->created_by)->get();

            }

            foreach($employee as $emp){
                if(!empty($emp->phone)){
                    $uArr = [
                        'event_name' => $request->title,
                        'branch_name' => $branch->name ?? $branch,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                    ];
                    SendMsg::SendMsgs($emp->phone, $uArr , 'New Event');
                }
            }
        }
    }
}

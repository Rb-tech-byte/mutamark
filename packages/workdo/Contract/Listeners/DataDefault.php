<?php

namespace Modules\Contract\Listeners;

use App\Events\DefaultData;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Contract\Entities\ContractUtility;

class DataDefault
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
    public function handle(DefaultData $event)
    {
        $company_id = $event->company_id;
        $workspace_id = $event->workspace_id ?? 0;
        $user_module = is_array($event->user_module) ? $event->user_module : (!empty($event->user_module) ? explode(',', $event->user_module) : []);
        if(!empty($user_module))
        {
            if (in_array("Contract", $user_module))
            {
                ContractUtility::defaultdata($company_id,$workspace_id);
            }
        }
    }
}

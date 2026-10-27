<?php

namespace Workdo\MobileServiceManagement\Listeners;

use App\Events\DefaultData;
use Workdo\MobileServiceManagement\Models\MobileServiceUtility;

class DataDefault
{
    public function __construct()
    {
        //
    }

    public function handle(DefaultData $event)
    {
        $company_id = $event->company_id;
        $user_module = $event->user_module ? explode(',', $event->user_module) : [];
        if(!empty($user_module))
        {
            if (in_array("MobileServiceManagement", $user_module))
            {
                MobileServiceUtility::defaultdata($company_id);
            }
        }
    }
}
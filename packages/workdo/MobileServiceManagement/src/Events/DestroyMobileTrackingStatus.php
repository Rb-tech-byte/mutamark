<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Workdo\MobileServiceManagement\Models\MobileTrackingStatus;

class DestroyMobileTrackingStatus
{
    use Dispatchable;

    public function __construct(
        public MobileTrackingStatus $mobiletrackingstatus,
    ) {}
}
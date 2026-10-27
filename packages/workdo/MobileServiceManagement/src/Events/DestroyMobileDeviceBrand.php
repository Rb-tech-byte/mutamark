<?php

namespace Workdo\MobileServiceManagement\Events;

use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyMobileDeviceBrand
{
    use Dispatchable;

    public function __construct(
        public MobileDeviceBrand $mobiledevicebrand
    ) {}
}
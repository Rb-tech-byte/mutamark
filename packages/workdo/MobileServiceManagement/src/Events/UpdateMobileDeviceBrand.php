<?php

namespace Workdo\MobileServiceManagement\Events;

use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateMobileDeviceBrand
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public MobileDeviceBrand $mobiledevicebrand
    ) {}
}
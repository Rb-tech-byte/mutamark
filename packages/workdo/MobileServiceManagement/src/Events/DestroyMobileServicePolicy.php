<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Workdo\MobileServiceManagement\Models\MobileServicePolicy;

class DestroyMobileServicePolicy
{
    use Dispatchable;

    public function __construct(
        public MobileServicePolicy $mobileservicepolicy
    ) {}
}
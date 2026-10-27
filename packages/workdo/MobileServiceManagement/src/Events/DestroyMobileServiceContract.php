<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Workdo\MobileServiceManagement\Models\MobileServiceContract;

class DestroyMobileServiceContract
{
    use Dispatchable;

    public function __construct(
        public MobileServiceContract $mobileservicecontract
    ) {}
}
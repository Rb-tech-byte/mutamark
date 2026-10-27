<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;

class CreateMobileServiceRequest
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public MobileServiceRequest $mobileservicerequest,
    ) {}
}
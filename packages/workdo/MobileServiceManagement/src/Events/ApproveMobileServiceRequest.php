<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;

class ApproveMobileServiceRequest
{
    use Dispatchable;

    public function __construct(
    public MobileServiceRequest $mobileservicerequest,
) {}
}
<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;

class ApproveMobileServicePendingRequest
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public MobileServiceRequest $pendingservicerequest,
    ) {}
}
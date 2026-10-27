<?php

namespace Workdo\MobileServiceManagement\Events;

use Workdo\MobileServiceManagement\Models\MobileServicePayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateMobileServicePayment
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public MobileServicePayment $payment
    ) {}
}
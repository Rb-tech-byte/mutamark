<?php

namespace Workdo\MobileServiceManagement\Events;

use Workdo\MobileServiceManagement\Models\MobileServicePayment;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyMobileServicePayment
{
    use Dispatchable;

    public function __construct(
        public MobileServicePayment $payment
    ) {}
}
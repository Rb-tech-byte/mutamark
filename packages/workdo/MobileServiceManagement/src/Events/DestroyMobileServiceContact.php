<?php

namespace Workdo\MobileServiceManagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Workdo\MobileServiceManagement\Models\MobileServiceContact;

class DestroyMobileServiceContact
{
    use Dispatchable;

    public function __construct(
    public MobileServiceContact $mobileservicecontact,
) {}
}
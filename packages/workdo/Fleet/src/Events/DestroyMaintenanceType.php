<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyMaintenanceType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $maintenanceType;

    public function __construct($maintenanceType)
    {
        $this->maintenanceType = $maintenanceType;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}

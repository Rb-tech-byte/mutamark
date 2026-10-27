<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyVehicleType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $vehicleType;

    public function __construct($vehicleType)
    {
        $this->vehicleType = $vehicleType;
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

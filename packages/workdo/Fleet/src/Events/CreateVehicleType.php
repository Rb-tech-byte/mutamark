<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class CreatevehicleType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $vehicleType;

    public function __construct($request ,$vehicleType)
    {
        $this->request = $request;
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

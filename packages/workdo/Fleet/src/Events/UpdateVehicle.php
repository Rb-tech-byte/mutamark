<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class UpdateVehicle
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $vehicle;

    public function __construct($request ,$vehicle)
    {
        $this->request = $request;
        $this->vehicle = $vehicle;
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

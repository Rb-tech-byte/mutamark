<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class CreateVehicle
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $Vehicle;

    public function __construct($request ,$Vehicle)
    {
        $this->request = $request;
        $this->Vehicle = $Vehicle;

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

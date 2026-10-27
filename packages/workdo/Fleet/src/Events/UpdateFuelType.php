<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class UpdateFuelType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $fuelType;

    public function __construct($request ,$fuelType)
    {
        $this->request = $request;
        $this->fuelType = $fuelType;
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

<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyFuelType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $fuelType;

    public function __construct($fuelType)
    {
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

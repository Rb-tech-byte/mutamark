<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class CreateMaintenances
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $Maintenances;

    public function __construct($request ,$Maintenances)
    {
        $this->request = $request;
        $this->Maintenances = $Maintenances;
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

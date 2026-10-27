<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class CreateMaintenanceType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $MaintenanceType;

    public function __construct($request ,$MaintenanceType)
    {
        $this->request = $request;
        $this->MaintenanceType = $MaintenanceType;
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

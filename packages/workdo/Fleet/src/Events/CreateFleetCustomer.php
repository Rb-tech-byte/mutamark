<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class CreateFleetCustomer
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $customers;

    public function __construct($request ,$customers)
    {
        $this->request = $request;
        $this->customers = $customers;


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

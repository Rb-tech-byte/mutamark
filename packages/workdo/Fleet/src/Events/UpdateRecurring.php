<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class Updaterecurring
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $recurring;

    public function __construct($request ,$recurring)
    {
        $this->request = $request;
        $this->recurring = $recurring;
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

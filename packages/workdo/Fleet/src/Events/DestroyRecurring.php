<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyRecurring
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $recurring;

    public function __construct($recurring)
    {
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

<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyLogbook
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $logbook;

    public function __construct($logbook)
    {
        $this->logbook = $logbook;
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

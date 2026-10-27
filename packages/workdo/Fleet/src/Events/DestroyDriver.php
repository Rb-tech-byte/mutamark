<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyDriver
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
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

<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class UpdateLogbook
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $logbook;

    public function __construct($request ,$logbook)
    {
        $this->request = $request;
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

<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class Updatelicense
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $license;

    public function __construct($request ,$license)
    {
        $this->request = $request;
        $this->license = $license;
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

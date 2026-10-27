<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyLicense
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $license;

    public function __construct($license)
    {
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

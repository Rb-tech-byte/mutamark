<?php

namespace Modules\Contract\Events;

use Illuminate\Queue\SerializesModels;

class DestroyContract
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $contract;

    public function __construct($contract)
    {
        $this->contract = $contract;
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

<?php

namespace Modules\Contract\Events;

use Illuminate\Queue\SerializesModels;

class DestroyContractType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $contractType;

    public function __construct($contractType)
    {
        $this->contractType = $contractType;
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

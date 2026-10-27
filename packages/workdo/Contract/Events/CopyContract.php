<?php

namespace Modules\Contract\Events;

use Illuminate\Queue\SerializesModels;

class CopyContract
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $contract;

    public function __construct($request,$contract)
    {
        $this->request = $request;
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

<?php

namespace Modules\Contract\Events;

use Illuminate\Queue\SerializesModels;

class UpdateContractType
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $contractType;

    public function __construct($request,$contractType)
    {
        $this->request = $request;
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

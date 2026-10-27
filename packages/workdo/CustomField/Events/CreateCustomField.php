<?php

namespace Modules\CustomField\Events;

use Illuminate\Queue\SerializesModels;

class CreateCustomField
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $custom_field;
    public function __construct($request,$custom_field)
    {
        $this->request = $request;
        $this->custom_field = $custom_field;
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

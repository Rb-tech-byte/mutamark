<?php

namespace Modules\CustomField\Events;

use Illuminate\Queue\SerializesModels;

class UpdateCustomField
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $customField;
    public function __construct($request,$customField)
    {
        $this->request = $request;
        $this->customField = $customField;
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

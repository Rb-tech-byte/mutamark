<?php

namespace Modules\Appointment\Events;

use Illuminate\Queue\SerializesModels;

class UpdateQuestion
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $question;
    public function __construct($request, $question)
    {
        $this->request = $request;
        $this->question = $question;
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

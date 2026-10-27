<?php

namespace Modules\Appointment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyQuestion
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $question;
    public function __construct($question)
    {
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

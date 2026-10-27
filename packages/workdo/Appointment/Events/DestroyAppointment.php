<?php

namespace Modules\Appointment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyAppointment
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $appointment;
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
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

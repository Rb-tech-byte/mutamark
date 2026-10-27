<?php

namespace Modules\Appointment\Events;

use Illuminate\Queue\SerializesModels;

class AppointmentCallbackEvent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $appointment_callback;

    public function __construct($request, $appointment_callback)
    {
        $this->request = $request;
        $this->appointment_callback = $appointment_callback;
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

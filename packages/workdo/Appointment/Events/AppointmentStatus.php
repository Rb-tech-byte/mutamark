<?php

namespace Modules\Appointment\Events;

use Illuminate\Queue\SerializesModels;

class AppointmentStatus
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $schedule;

    public function __construct($request, $schedule)
    {
        $this->request = $request;
        $this->schedule = $schedule;
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

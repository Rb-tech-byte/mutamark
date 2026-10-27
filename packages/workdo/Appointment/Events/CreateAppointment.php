<?php

namespace Modules\Appointment\Events;

use Illuminate\Queue\SerializesModels;

class CreateAppointment
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $post;
    public function __construct($request, $post)
    {
        $this->request = $request;
        $this->post = $post;
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

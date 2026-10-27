<?php

namespace Workdo\Fleet\Events;

use Illuminate\Queue\SerializesModels;

class DestroyFleetPayment
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
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

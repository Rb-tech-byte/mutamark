<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobCandidate
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $job_candidate;

    public function __construct($job_candidate)
    {
        $this->job_candidate = $job_candidate;
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

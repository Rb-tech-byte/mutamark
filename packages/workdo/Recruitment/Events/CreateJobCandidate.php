<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class CreateJobCandidate
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_candidate;

    public function __construct($request, $job_candidate)
    {
        $this->request = $request;
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

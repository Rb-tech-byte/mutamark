<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class CreateJobExperienceCandidate
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_experience_candidate;

    public function __construct($request, $job_experience_candidate)
    {
        $this->request = $request;
        $this->job_experience_candidate = $job_experience_candidate;
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

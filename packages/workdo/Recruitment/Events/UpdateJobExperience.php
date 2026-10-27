<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class UpdateJobExperience
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_experience;

    public function __construct($request, $job_experience)
    {
        $this->request = $request;
        $this->job_experience = $job_experience;
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

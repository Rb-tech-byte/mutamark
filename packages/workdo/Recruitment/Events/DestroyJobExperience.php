<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobExperience
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $job_experience;

    public function __construct($job_experience)
    {
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

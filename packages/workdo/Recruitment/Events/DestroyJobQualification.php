<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobQualification
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $job_qualification;

    public function __construct($job_qualification)
    {
        $this->job_qualification = $job_qualification;
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

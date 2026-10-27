<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class CreateJobQualification
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_qualification;

    public function __construct($request, $job_qualification)
    {
        $this->request = $request;
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

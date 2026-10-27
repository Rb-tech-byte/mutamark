<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class CreateJobProject
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_project;

    public function __construct($request, $job_project)
    {
        $this->request = $request;
        $this->job_project = $job_project;
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

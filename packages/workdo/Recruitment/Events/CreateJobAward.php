<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class CreateJobAward
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_award;

    public function __construct($request, $job_award)
    {
        $this->request = $request;
        $this->job_award = $job_award;
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

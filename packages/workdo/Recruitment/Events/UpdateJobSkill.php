<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class UpdateJobSkill
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $job_skill;

    public function __construct($request, $job_skill)
    {
        $this->request = $request;
        $this->job_skill = $job_skill;
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

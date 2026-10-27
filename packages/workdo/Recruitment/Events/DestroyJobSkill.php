<?php

namespace Modules\Recruitment\Events;

use Illuminate\Queue\SerializesModels;

class DestroyJobSkill
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $job_skill;

    public function __construct($job_skill)
    {
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

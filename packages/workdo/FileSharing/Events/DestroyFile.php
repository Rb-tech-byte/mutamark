<?php

namespace Modules\FileSharing\Events;

use Illuminate\Queue\SerializesModels;

class DestroyFile
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $file;

    public function __construct($file)
    {
        $this->file = $file;
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

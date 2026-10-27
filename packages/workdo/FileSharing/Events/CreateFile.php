<?php

namespace Modules\FileSharing\Events;

use Illuminate\Queue\SerializesModels;

class CreateFile
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $file;

    public function __construct($request, $file)
    {
        $this->request = $request;
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

<?php

namespace Modules\FileSharing\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFileshare extends Mailable
{
    use Queueable, SerializesModels;
    public $file;
    // public $emailData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('filesharing::email.user_email')
        ->subject('Attachment File')
        ->with([
            'file' => $this->file,
        ])
        ->attach($this->file->file_path);
    }

}

<?php

namespace Modules\MailBox\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class sendMail extends Mailable
{
    
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $subject;
    public $message;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message)
    {
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       
        return $this->from(company_setting('mail_from_address'))
                    ->subject($this->subject)
                    ->markdown('mailbox::mail.email_template')
                    ->with([
                        'content' => $this->message,
                    ]);

    }
}

<?php

namespace Modules\MailBox\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailboxCredentail extends Model
{
    use HasFactory;

    protected $tabel = "mailbox_credentails";
    protected $fillable = [
        "emailbox_mail_driver",
        "emailbox_mail_host",
        "emailbox_outgoing_port",
        "emailbox_incoming_port",
        "emailbox_mail_username",
        "emailbox_mail_from_address",
        "emailbox_mail_password",
        "emailbox_mail_encryption",
        "emailbox_mail_from_name",
        "workspace_id",
        "created_by"
    ];
    
    protected static function newFactory()
    {
        return \Modules\MailBox\Database\factories\MailboxCredenntailFactory::new();
    }
}

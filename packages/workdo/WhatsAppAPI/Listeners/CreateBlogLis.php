<?php

namespace Modules\WhatsAppAPI\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\WhatsAppAPI\Entities\SendMsg;
use Modules\LMS\Events\CreateBlog;
use Illuminate\Support\Facades\Auth;
class CreateBlogLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CreateBlog $event)
    {
        if(module_is_active('WhatsAppAPI') && company_setting('whatsappapi_notification_is') == 'on' && !empty(company_setting('Whatsappapi New Blog')) && company_setting('Whatsappapi New Blog')  == true)
        {
            $blog = $event->blog;
            $to = Auth::user()->mobile_no;
            if(!empty($blog) && !empty($to))
            {
                $store = \Modules\LMS\Entities\Store::where('workspace_id',getActiveWorkSpace())->first();

                $uArr = [
                    'blog_name' => $blog->title,
                    'store_name' => $store->name
                ];
                SendMsg::SendMsgs($to ,$uArr , 'New Blog');
            }
        }
    }
}

<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\LMS\Events\CreateBlog;
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
        if(module_is_active('SMS') && !empty(company_setting('SMS New Blog')) && company_setting('SMS New Blog')  == true)
        {
            $blog = $event->blog;
            if(!empty($blog))
            {
                $store = \Workdo\LMS\Entities\Store::where('workspace_id',getActiveWorkSpace())->first();

                $uArr = [
                    'blog_name' => $blog->title,
                    'store_name' => $store->name
                ];
                $to = Auth::user()->mobile_no;
                SendMsg::SendMsgs($to ,$uArr , 'New Blog');
            }
        }
    }
}

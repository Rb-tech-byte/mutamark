<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Internalknowledge\Events\CreateArticle;
use Workdo\Internalknowledge\Entities\Book;
use App\Models\User;

class CreateArticleLis
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
    public function handle(CreateArticle $event)
    {
        $article = $event->article;
        $book = Book::find($article->book);
        $user = User::find($article->created_by);

        if (module_is_active('SMS')  && company_setting('sms_notification_is')=='on' && !empty(company_setting('SMS New Article')) && company_setting('SMS New Article')  == true) {

            if(!empty($user->mobile_no))
            {
                $uArr = [
                    'article_type' => $article->type,
                    'book_name' => !empty($book) ? $book->name : '-',
                ];

                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Article');
            }

        }
    }
}

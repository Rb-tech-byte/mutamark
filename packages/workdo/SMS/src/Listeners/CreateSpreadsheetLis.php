<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\Spreadsheet\Events\CreateSpreadsheet;
use App\Models\User;
class CreateSpreadsheetLis
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
    public function handle(CreateSpreadsheet $event)
    {
        $spreadsheets = $event->spreadsheets;
        $user = User::find($spreadsheets->created_by);

        if (module_is_active('SMS') && !empty(company_setting('SMS New Spreadsheet')) && company_setting('SMS New Spreadsheet')  == true) {

            if(!empty($user) && !empty($user->mobile_no))
            {
                $uArr = [
                    'user_name' => $user->name
                ];
                SendMsg::SendMsgs($user->mobile_no , $uArr , 'New Spreadsheet');
            }

        }
    }
}

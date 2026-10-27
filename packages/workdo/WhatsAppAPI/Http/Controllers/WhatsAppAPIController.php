<?php

namespace Modules\WhatsAppAPI\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Setting;

class WhatsAppAPIController extends Controller
{
 
    public function setting(Request $request)
    {
        if(\Auth::user()->isAbleTo('whatsappapi manage'))
        {
            $validator = \Validator::make($request->all(),
            [
                'whatsappapi_notification_is' => 'required',
            ]);
            if($validator->fails()){
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            else
            {
                $getActiveWorkSpace = getActiveWorkSpace();
                $creatorId = creatorId();


                if($request->has('whatsappapi_notification_is')){
                    $post['whatsappapi_notification_is'] = 'on';
                }else{
                    $post('whatsappapi_notification_is', 'off');
                }
    
                if($request->has('whatsapp_phone_number_id') && $request->has('whatsapp_access_token')){
                    $post['whatsapp_phone_number_id']   = $request->whatsapp_phone_number_id;
                    $post['whatsapp_access_token']      = $request->whatsapp_access_token;
                }
                
                if($request->has('whatsappapi'))
                {
                    foreach($request->whatsappapi as $key => $notification)
                    {
                        $post[$key] =  $notification;
                    }
                }
                foreach ($post as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key' => $key,
                        'workspace' => $getActiveWorkSpace,
                        'created_by' => $creatorId,
                    ];

                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
                // Settings Cache forget
                comapnySettingCacheForget();
                return redirect()->back()->with('success','Whatsapp setting save sucessfully.');
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function testMassage(Request $request)
    {
        $data                    = [];
        $data['whatsapp_phone_number_id']     = $request->whatsapp_phone_number_id;
        $data['whatsapp_access_token']       = $request->whatsapp_access_token;

        return view('whatsappapi::company.settings.test_massage', compact('data'));
    }

    public function sendTestMassage(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'mobile' => 'required',
                               'whatsapp_phone_number_id' => 'required',
                               'whatsapp_access_token' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return error_res($messages->first());
        }
        try
        {

            $url = 'https://graph.facebook.com/v17.0/'.$request->whatsapp_phone_number_id.'/messages';

            $data = array(
                'messaging_product' => 'whatsapp',
                'to' => $request->mobile,
                'type' => 'template',
                'template' => array(
                    'name' => 'hello_world',
                    'language' => array(
                        'code' => 'en_US'
                    )
                )
            );

            $headers = array(
                'Authorization: Bearer '.$request->whatsapp_access_token,
                'Content-Type: application/json'
            );

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);

            $responseData = json_decode($response);

            curl_close($ch);

            if(isset($responseData->error)){

                return error_res($responseData->error->message);

            }else{

                return success_res(__('Massage send Successfully'));
            }
        }
        catch(\Exception $e)
        {
            return error_res($e->getMessage());
        }
    }

}

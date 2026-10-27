<?php

namespace Workdo\SMS\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tzsk\Sms\Facades\Sms;
use App\Models\NotificationTemplateLang;
use App\Models\Notification;
use App\Models\User;

class SendMsg extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Workdo\SMS\Database\factories\SendMsgFactory::new();
    }
    public static $sms_settings=[
        "sns" => "AWS",
        "twilio" => "Twilio",
        "clockwork" => "Clockwork",
        "melipayamak" => "Melipayamak",
        "kavenegar" => "Kavenegar",
        "smsgatewayme" => "SMS Gateway Me",

    ];

    public static function SendMsgs($mobile_no, $uArr, $action , $company_id = null, $workspace_id = null){
        $usr = \Auth::user();
        $template = Notification::where('action', $action)->where('type','SMS')->first();
        if(empty($usr)){
            $usr = User::find($company_id);
        }
        if(!empty($usr)){
            $content = NotificationTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();

        }else{
            $content = NotificationTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();

        }
        $msg = self::replaceVariable($content->content, $uArr ,$company_id , $workspace_id);

        $company_settings = getCompanyAllSetting($company_id ,$workspace_id);
        if($company_settings['sms_notification_is'] == "on"){

           self::active_driver($company_id ,$workspace_id );
        }

        try {
                if($company_settings['sms_notification_is'] == "on"){
                $response = Sms::via($company_settings['sms_setting'])->send($msg, function($sms) use ($mobile_no) {
                    $sms->to($mobile_no);
                });
        }
        } catch (\Exception $e) {
        }


    }

    public static function replaceVariable($content, $obj ,$company_id = null, $workspace_id = null )
    {
        $arrVariable = [
            '{user_name}',
            '{company_name}',
            '{invoice_id}',
            '{workspace_name}',
            '{bill_id}',
            '{amount}',
            '{vender_name}',
            '{appointment_name}',
            '{date}',
            '{time}',
            '{business_name}',
            '{status}',

            '{component_name}',
            '{wo_name}',
            '{part_name}',
            '{location_name}',
            '{contract_number}',
            '{name}',

            '{month}',
            '{award_name}',
            '{event_name}',
            '{branch_name}',
            '{start_date}',
            '{end_date}',
            '{purpose_of_visit}',
            '{place_of_visit}',
            '{announcement_name}',

            '{lead_name}',
            '{old_stage}',
            '{new_stage}',
            '{deal_name}',

            '{purchase_id}',
            '{job_name}',
            '{application}',
            '{retainer_id}',
            '{start_time}',
            '{end_time}',
            '{quotation_id}',
            '{sales_order_id}',
            '{sales_invoice_id}',

            '{payment_type}',
            '{meeting_name}',
            '{ticket_name}',
            '{project_name}',
            '{task_name}',
            '{bug_name}',
            '{contact_name}',

            '{coupon_name}',
            '{discount}',
            '{booking_number}',
            "{price}",
            "{portfolio_name}",
            "{portfolio_category}",
            '{module}',

            '{supplier_name}',
            '{location}',
            '{assets}',
            '{asset}',
            '{program_name}',
            '{order_number}',

            '{insurance_provider}',
            '{old_status}',
            '{new_status}',
            '{store_name}',
            '{course_name}',

            '{student_name}',
            '{blog_name}',
            '{page_name}',
            '{warehouse_name}',

            '{fleet_name}',
            '{process_name}',
            '{hours}',
            '{cycle_name}',
            '{office_name}',
            '{department}',
            '{season_name}',
            '{season}',
            '{crop_name}',
            '{activity}',
            '{cultivation}',
            '{activity_name}',
            '{service_name}',
            '{cultivation_name}',
            '{farmer_name}',

            '{submodule_name}',
            '{module_name}',
            '{tour_name}',
            '{days}',
            '{agent_name}',
            '{journalist_name}',
            '{information}',
            '{newspaper_name}',
            '{advertidsement}',

            '{teacher_name}',
            '{parent_name}',
            '{class_name}',
            '{services}',
            '{team_name}',

            '{property_name}',
            '{unit_name}',
            '{vehicle_name}',
            '{child_name}',
            '{product_name}',
            '{consignment_name}',
            '{commission}',
            '{machine_name}',
            '{doctor_name}',
            '{patient_name}',
            '{specialization}',
            '{homework_title}',
            '{subject_name}',

            '{employee_name}',
            '{note_type}',
            '{article_type}',
            '{book_name}',
            '{position}',
            '{challenge}',
            '{type}',
        ];
        $arrValue    = [
            'user_name' => '-',
            'company_name' => '-',
            'invoice_id' => '-',
            'workspace_name'=>'-',
            'bill_id'=>'-',

            'amount'=>'-',
            'vender_name'=>'-',

            'appointment_name'=>'-',
            'date'=>'-',
            'time'=>'-',
            'business_name'=>'-',
            'status'=>'',

            'component_name'=> '-',
            'wo_name'=>'-',
            'part_name'=>'-',
            'location_name'=>'-',
            'contract_number'=>'-',
            'name'=>'-',

            'month'=>'-',
            'award_name'=>'-',
            'event_name'=>'-',
            'branch_name'=>'-',
            'start_date'=>'-',
            'end_date'=>'-',
            'purpose_of_visit'=>'-',
            'place_of_visit'=>'-',
            'announcement_name'=>'-',

            'lead_name' => '-',
            'old_stage'=>'-',
            'new_stage' => '-',
            'deal_name'=>'-',

            'purchase_id'=>'-',
            'job_name'=>'-',
            'application'=>'-',
            'retainer_id' => '-',
            'start_time'=>'-',
            'end_time'=>'-',
            'quotation_id'=>'-',
            'sales_order_id'=>'-',
            'sales_invoice_id'=>'-',

            'payment_type'=>'-',
            'meeting_name'=>'-',
            'ticket_name'=>'-',
            'project_name'=>'-',
            'task_name'=>'-',
            'bug_name'=>'-',
            'contact_name'=>'-',

            'coupon_name'=>'-',
            'discount'=>'-',
            'booking_number'=>'-',
            'price'=>'-',
            'portfolio_name'=>'-',
            'portfolio_category'=>'-',
            'module'=>'-',

            'supplier_name'=>'-',
            'location'=>'-',
            'assets'=>'-',
            'asset'=>'-',
            'program_name'=>'-',
            'order_number'=>'-',

            'insurance_provider'=>'-',
            'old_status'=>'-',
            'new_status'=>'-',
            'store_name'=>'-',
            'course_name'=>'-',

            'student_name' => '-',
            'blog_name' => '-',
            'page_name' => '-',
            'warehouse_name'=>'-',

            'fleet_name'=>'-',
            'process_name'=>'-',
            'hours'=>'-',
            'cycle_name'=>'-',
            'office_name'=>'-',
            'department'=>'-',
            'season_name'=>'-',
            'season'=>'-',
            'crop_name'=>'-',
            'activity'=>'-',
            'cultivation'=>'-',
            'activity_name'=>'-',
            'service_name'=>'-',
            'cultivation_name'=>'-',
            'farmer_name'=>'-',

            'submodule_name'=>'-',
            'module_name'=>'-',
            'tour_name'=>'-',
            'days' => '-',
            'agent_name' => '-',
            'journalist_name' => '-',
            'information'=>'-',
            'newspaper_name'=>'-',
            'advertidsement' => '-',

            'teacher_name' => '-',
            'parent_name' => '-',
            'class_name' => '-',
            'services' => '-',
            'team_name' => '-',

            'property_name'=>'-',
            'unit_name' => '-',
            'vehicle_name' => '-',
            'child_name' => '-',
            'product_name' => '-',
            'consignment_name' => '-',
            'commission'=>'-',
            'machine_name' => '-',
            'doctor_name'=>'-',
            'patient_name'=>'-',
            'specialization'=>'-',
            'homework_title'=>'-',
            'subject_name'=>'-',
            'employee_name'=>'-',
            'note_type'=>'-',
            'article_type'=>'-',
            'book_name'=>'-',
            'position'=> '-',
            'challenge'=>'-',
            'type'=>'-',
        ];
        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }

        $workspace = \App\Models\WorkSpace::find(getActiveWorkSpace());
        if(!empty($workspace)){
            $arrValue['company_name'] = \Auth::user()->name;
            $arrValue['workspace_name'] = $workspace->name;
        }else{
            $user = User::find($company_id);
            $workspace = \App\Models\WorkSpace::find(getActiveWorkSpace($company_id));
            $arrValue['company_name'] = $user->name;
            $arrValue['workspace_name'] = $workspace->name;
        }
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static function active_driver($company_id = null, $workspace_id = null){
        $company_settings = getCompanyAllSetting($company_id ,$workspace_id);
            if($company_settings['sms_setting'] == "twilio" ){

                $twilio_sid = isset($company_settings['sms_twilio_sid']) ? $company_settings['sms_twilio_sid'] : '';
                $twilio_token = isset($company_settings['sms_twilio_token']) ? $company_settings['sms_twilio_token'] : '';
                $twilio_from = isset($company_settings['sms_twilo_from_number']) ? $company_settings['sms_twilo_from_number'] : '';

                config(
                    [
                        'sms.drivers.twilio.sid' => $twilio_sid,
                        'sms.drivers.twilio.token' => $twilio_token,
                        'sms.drivers.twilio.from' => $twilio_from,
                    ]
                );
            }elseif($company_settings['sms_setting'] == "sns"){
                $sns_access_key = isset($company_settings['sns_access_key']) ? $company_settings['sns_access_key'] : '';
                $sns_secret_key = isset($company_settings['sns_secret_key']) ? $company_settings['sns_secret_key'] : '';
                $sns_region = isset($company_settings['sns_region']) ? $company_settings['sns_region'] : '';
                $sns_sender_id = isset($company_settings['sns_sender_id']) ? $company_settings['sns_sender_id'] : '';
                $sns_type = isset($company_settings['sns_type']) ? $company_settings['sns_type'] : '';

                config(
                    [
                        'sms.drivers.sns.sid' => $sns_access_key,
                        'sms.drivers.sns.token' => $sns_secret_key,
                        'sms.drivers.sns.from' => $sns_region,
                        'sms.drivers.sns.from' => $sns_sender_id,
                        'sms.drivers.sns.from' => $sns_type,
                    ]
                );

            }elseif($company_settings['sms_setting'] == "clockwork"){
                $clockwork_api_key = isset($company_settings['clockwork_api_key']) ? $company_settings['clockwork_api_key'] : '';


                config(
                    [
                        'sms.drivers.clockwork.key' => $clockwork_api_key,

                    ]
                );
            }elseif($company_settings['sms_setting'] == "melipayamak"){
                $melipayamak_username = isset($company_settings['melipayamak_username']) ? $company_settings['melipayamak_username'] : '';
                $melipayamak_password = isset($company_settings['melipayamak_password']) ? $company_settings['melipayamak_password'] : '';
                $melipayamak_from_number = isset($company_settings['melipayamak_from_number']) ? $company_settings['melipayamak_from_number'] : '';
                config(
                    [
                        'sms.drivers.melipayamak.username' => $melipayamak_username,
                        'sms.drivers.melipayamak.password' => $melipayamak_password,
                        'sms.drivers.melipayamak.from' => $melipayamak_from_number,
                        'sms.drivers.melipayamak.flash' => false,

                    ]
                );
            }elseif($company_settings['sms_setting'] == "kavenegar"){
                $kavenegar_apiKey = isset($company_settings['kavenegar_apiKey']) ? $company_settings['kavenegar_apiKey'] : '';
                $kavenegar_from_number = isset($company_settings['kavenegar_from_number']) ? $company_settings['kavenegar_from_number'] : '';
                config(
                    [
                        'sms.drivers.kavenegar.apiKey' => $kavenegar_apiKey,
                        'sms.drivers.kavenegar.from' => $kavenegar_from_number,

                    ]
                );
            }else{
                $smsgatewayme_apiToken = isset($company_settings['smsgatewayme_apiToken']) ? $company_settings['smsgatewayme_apiToken'] : '';
                $Smsgatewayme_device_id = isset($company_settings['Smsgatewayme_device_id']) ? $company_settings['Smsgatewayme_device_id'] : '';
                config(
                    [
                        'sms.drivers.smsgatewayme.apiToken' => $smsgatewayme_apiToken,
                        'sms.drivers.smsgatewayme.from' => $Smsgatewayme_device_id,

                    ]
                );
            }


    }
}

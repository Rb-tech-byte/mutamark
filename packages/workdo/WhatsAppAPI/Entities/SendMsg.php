<?php

namespace Modules\WhatsAppAPI\Entities;

use App\Models\Notification;
use App\Models\NotificationTemplateLang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SendMsg extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\WhatsAppAPI\Database\factories\SendMsgFactory::new ();
    }

    public static function SendMsgs($mobile_no, $uArr, $action, $user_id = null , $workspace_id= null)
    {
        $usr = Auth::user();
        if (empty($usr)) {
            $usr = User::find($user_id);
        }
        $template = Notification::where('action', $action)->where('type', 'WhatsAppAPI')->first();

        $content = NotificationTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();

        if ($content == null) {
            $content = NotificationTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();
        }

        if(!empty($user_id))
        {
            if(!empty($workspace_id))
            {
                $workspace_id= $workspace_id;
            }else
            {
                $workspace_id= getActiveWorkSpace($user_id);
            }
        }

        $msg = self::replaceVariable($content->content, $uArr ,$user_id , $workspace_id);
        $CompanyAllSetting = getCompanyAllSetting($user_id);

        $whatsappapi_notification_is = isset($CompanyAllSetting['whatsappapi_notification_is']) ? $CompanyAllSetting['whatsappapi_notification_is'] : '';
        $whatsapp_phone_number_id = isset($CompanyAllSetting['whatsapp_phone_number_id']) ? $CompanyAllSetting['whatsapp_phone_number_id'] : '';
        $whatsapp_access_token = isset($CompanyAllSetting['whatsapp_access_token']) ? $CompanyAllSetting['whatsapp_access_token'] : '';

        if (($whatsappapi_notification_is == 'on') && (!empty($whatsapp_phone_number_id)) && (!empty($whatsapp_access_token))) {
            try
            {

                $url = 'https://graph.facebook.com/v17.0/' . $whatsapp_phone_number_id . '/messages';

                $data = array(
                    'messaging_product' => 'whatsapp',
                    // 'recipient_type' => 'individual',
                    'to' => $mobile_no,
                    'type' => 'text',
                    'text' => array(
                        'preview_url' => false,
                        'body' => $msg,
                    ),
                );

                $headers = array(
                    'Authorization: Bearer ' . $whatsapp_access_token,
                    'Content-Type: application/json',
                );

                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                $responseData = json_decode($response);

                curl_close($ch);

            } catch (\Exception $e) {
                return $e;
            }
        } else {
            return false;
        }
    }

    public static function replaceVariable($content, $obj ,$company_id , $workspace_id)
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
        ];

        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }

        $user = Auth::user();
        $workspace = \App\Models\WorkSpace::find(getActiveWorkSpace());
        if(empty($user)){
            $user = User::find($company_id);
            $workspace = \App\Models\WorkSpace::find(getActiveWorkSpace($company_id));
        }
        $arrValue['company_name'] = $user->name;
        $arrValue['workspace_name'] = $workspace->name;

        return str_replace($arrVariable, array_values($arrValue), $content);
    }
}

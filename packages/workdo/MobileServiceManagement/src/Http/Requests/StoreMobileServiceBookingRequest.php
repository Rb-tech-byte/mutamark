<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileServiceBookingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'firstName'        => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string',
            'serviceType'     => 'required|string',
            'deviceBrand'     => 'required|string',
            'deviceModel'     => 'required|string|max:255',
            'urgency'         => 'required|string|in:standard,priority,emergency',
            'issueDescription' => 'required|string',
            'specialRequests' => 'nullable|string',
            'attachments'     => 'nullable',
            'termsAgreed'     => 'nullable',
            'trackingStatus'  => 'nullable|string',
        ];
    }
}
<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileServicePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'mobile_service_id' => 'required|exists:product_service_items,id',
            'payment_date' => 'required|date',
            'price' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'repair_charge' => 'nullable|numeric|min:0',
            'extra_charges' => 'nullable|array',
            'extra_charges.*.text' => 'nullable|string|max:255',
            'extra_charges.*.price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }
}

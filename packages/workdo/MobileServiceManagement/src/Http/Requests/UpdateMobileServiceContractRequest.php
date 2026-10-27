<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMobileServiceContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|max:100',
            'contract_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'contract_details' => 'nullable|max:1000'
        ];
    }
}
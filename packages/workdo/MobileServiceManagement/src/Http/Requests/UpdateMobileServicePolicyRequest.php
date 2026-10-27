<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMobileServicePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'policy_name' => 'required|max:100',
            'description' => 'nullable|max:500',
            'response_time' => 'required|date_format:H:i',
            'resolution_time' => 'required|date_format:H:i'
        ];
    }
}
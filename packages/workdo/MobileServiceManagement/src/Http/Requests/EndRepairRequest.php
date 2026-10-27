<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EndRepairRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'repair_cost' => 'required|numeric|min:0',
            'repair_notes' => 'nullable|string'
        ];
    }
}
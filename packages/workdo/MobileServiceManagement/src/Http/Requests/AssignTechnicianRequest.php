<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTechnicianRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'technician_id' => 'required|exists:users,id',
            'estimated_completion_date' => 'required|date'
        ];
    }
}
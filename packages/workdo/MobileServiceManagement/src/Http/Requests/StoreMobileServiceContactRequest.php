<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileServiceContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'repair_type' => 'required|string|max:255',
            'message'     => 'required|string|max:1000',
        ];
    }
}
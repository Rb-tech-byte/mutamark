<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMobileTrackingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon_name' => 'required|string|max:255',
            'status_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'status_name' => 'required|string|max:255'
        ];
    }
}
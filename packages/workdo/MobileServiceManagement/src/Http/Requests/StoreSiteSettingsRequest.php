<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_logo' => 'nullable|string',
            'footer_logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:500',
            'footer_description' => 'nullable|string|max:1000',
        ];
    }
}
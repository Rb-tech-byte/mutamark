<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAboutSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'about_title' => 'required|string|max:255',
            'about_description' => 'nullable|string|max:1000',
            'about_image' => 'nullable|string',
            'about_image_text' => 'nullable|string|max:255',
            'about_facilities' => 'nullable|array',
            'about_facilities.*.title' => 'required|string|max:255',
            'about_facilities.*.description' => 'nullable|string|max:500',
        ];
    }
}
<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banner_title' => 'required|string|max:255',
            'banner_subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'image_icon' => 'nullable|string|max:255',
            'image_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
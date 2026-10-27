<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomeSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_tracking_title' => 'nullable|string|max:255',
            'service_tracking_description' => 'nullable|string|max:1000',
            'expertise_title' => 'nullable|string|max:255',
            'expertise_description' => 'nullable|string|max:1000',
            'cta_title' => 'nullable|string|max:255',
            'cta_description' => 'nullable|string|max:1000',
            'testimonial_title' => 'nullable|string|max:255',
            'testimonial_description' => 'nullable|string|max:1000',
        ];
    }
}
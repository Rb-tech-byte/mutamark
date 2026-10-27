<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactUsSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_title' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_emergency' => 'nullable|string|max:255',
            'location_title' => 'nullable|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'repair_center_title' => 'nullable|string|max:255',
            'repair_center_description' => 'nullable|string|max:1000',
            'repair_center_map_code' => 'nullable|string|regex:/<iframe[^>]*>.*<\/iframe>/i',
            'hours_title' => 'nullable|string|max:255',
            'service_hours' => 'nullable|array',
            'service_hours.*.status' => 'nullable|in:open,closed',
            'service_hours.*.opening_time' => 'nullable|string',
            'service_hours.*.closing_time' => 'nullable|string',
        ];
    }
}
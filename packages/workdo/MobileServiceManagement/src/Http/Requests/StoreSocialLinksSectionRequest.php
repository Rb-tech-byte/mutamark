<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialLinksSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'social_links' => 'nullable|array',
            'social_links.*.icon' => 'required|string',
            'social_links.*.url' => 'required|url',
        ];
    }
}
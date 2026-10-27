<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqsSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required|string|max:500',
            'faqs.*.answer' => 'required|string|max:2000',
        ];
    }
}
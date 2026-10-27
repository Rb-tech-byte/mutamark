<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomPagesSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'contents' => 'required|string',
            'enable_page_footer' => 'required|boolean',
        ];
    }
}
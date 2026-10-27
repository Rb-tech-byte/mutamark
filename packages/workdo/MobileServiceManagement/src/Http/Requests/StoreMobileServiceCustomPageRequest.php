<?php

namespace Workdo\MobileServiceManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMobileServiceCustomPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'title'              => 'required|string|max:255',
            'contents'           => 'required|string',
            'enable_page_footer' => 'nullable|string',
            'slug'               => 'required|string|max:50|unique:mobile_service_custom_pages,slug,NULL,id,created_by,' . creatorId(),

        ];
    }
    public function messages(): array
    {
        return [
            'slug.unique' => 'Slug already exists, please choose a different one.'
        ];
    }
}

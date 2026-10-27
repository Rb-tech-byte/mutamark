<?php

namespace Workdo\ProductService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductServiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-product-service-item') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:product_service_items,sku,NULL,id,created_by,' . creatorId(),
            'tax_ids' => 'required|array',
            'tax_ids.*' => ['integer', 'distinct', \Illuminate\Validation\Rule::exists('product_service_taxes','id')->where('created_by',creatorId())],
            'category_id' => ['required', \Illuminate\Validation\Rule::exists('product_service_categories','id')->where('created_by',creatorId())],
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'unit' => ['required', \Illuminate\Validation\Rule::exists('product_service_units','id')->where('created_by',creatorId())],
            'quantity' => 'nullable|numeric|min:0|required_unless:type,service',
            'image' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'warehouse_id' => ['nullable', 'required_unless:type,service', \Illuminate\Validation\Rule::exists('warehouses','id')->where('created_by',creatorId())],
            'type' => 'required|in:product,service,part',
        ];
    }
}

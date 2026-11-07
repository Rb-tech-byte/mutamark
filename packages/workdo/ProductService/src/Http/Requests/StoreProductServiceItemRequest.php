<?php

namespace Workdo\ProductService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductServiceItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-product-service-item') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('category_id') && ($this->category_id === 'none' || $this->category_id === '')) {
            $this->merge(['category_id' => null]);
        }
        if ($this->has('unit') && ($this->unit === 'none' || $this->unit === '')) {
            $this->merge(['unit' => null]);
        }
        if ($this->has('warehouse_id') && ($this->warehouse_id === 'none' || $this->warehouse_id === '')) {
            $this->merge(['warehouse_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:product_service_items,sku,NULL,id,created_by,' . creatorId(),
            'tax_ids' => 'nullable|array',
            'tax_ids.*' => ['integer', 'distinct', \Illuminate\Validation\Rule::exists('product_service_taxes','id')->where('created_by',creatorId())],
            'category_id' => ['nullable', \Illuminate\Validation\Rule::exists('product_service_categories','id')->where('created_by',creatorId())],
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'sale_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'unit' => ['nullable', \Illuminate\Validation\Rule::exists('product_service_units','id')->where('created_by',creatorId())],
            'quantity' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'warehouse_id' => ['nullable', \Illuminate\Validation\Rule::exists('warehouses','id')->where('created_by',creatorId())],
            'type' => 'required|in:product,service,part',
        ];
    }
}

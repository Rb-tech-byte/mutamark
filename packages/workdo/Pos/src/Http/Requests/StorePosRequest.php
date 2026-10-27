<?php

namespace Workdo\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-pos');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', \Illuminate\Validation\Rule::exists('users','id')->where('created_by',creatorId())],
            'warehouse_id' => ['required', \Illuminate\Validation\Rule::exists('warehouses','id')->where('created_by',creatorId())],
            'billing_counter_id' => ['required', \Illuminate\Validation\Rule::exists('pos_billing_counters','id')->where('created_by',creatorId())],
            'pos_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.id' => ['required', \Illuminate\Validation\Rule::exists('product_service_items','id')->where('created_by',creatorId())],
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.item_discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.item_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => 'nullable|string|max:100',
            'paid_amount' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'billing_counter_id.required' => __('Please select a billing counter before creating POS.'),
        ];
    }
}
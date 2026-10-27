<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Workdo\ProductService\Models\WarehouseStock;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hasItems = $this->has('items') && is_array($this->input('items')) && count($this->input('items')) > 0;

        return [
            'from_warehouse' => ['required', Rule::exists('warehouses', 'id')->where('created_by', creatorId())],
            'to_warehouse' => ['required', 'different:from_warehouse', Rule::exists('warehouses', 'id')->where('created_by', creatorId())],
            'product_id' => [$hasItems ? 'nullable' : 'required', Rule::exists('product_service_items', 'id')->where('created_by', creatorId())],
            'quantity' => $hasItems ? 'nullable|numeric|min:0.01' : [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $warehouseStock = WarehouseStock::where('warehouse_id', $this->from_warehouse)
                        ->where('product_id', $this->product_id)
                        ->first();

                    if (!$warehouseStock || $value > $warehouseStock->quantity) {
                        $availableQty = $warehouseStock ? $warehouseStock->quantity : 0;
                        $fail("Quantity cannot exceed available stock ({$availableQty}).");
                    }
                }
            ],
            'date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:date',
            'driver_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:191',
            'shipping_carrier' => 'nullable|string|max:191',
            'transit_notes' => 'nullable|string',
            'initial_status' => 'nullable|string|in:draft,in_transit,completed',
            'items' => 'nullable|array',
            'items.*.product_id' => ['required_with:items', Rule::exists('product_service_items', 'id')->where('created_by', creatorId())],
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.from_bin_id' => ['nullable', Rule::exists('warehouse_locations', 'id')->where('warehouse_id', $this->input('from_warehouse'))],
            'items.*.to_bin_id' => ['nullable', Rule::exists('warehouse_locations', 'id')->where('warehouse_id', $this->input('to_warehouse'))],
            'items.*.notes' => 'nullable|string|max:255',
        ];
    }
}

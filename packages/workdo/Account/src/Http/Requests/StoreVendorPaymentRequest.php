<?php

namespace Workdo\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreVendorPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create-vendor-payments');
    }

    public function rules()
    {
        return [
            'payment_date' => 'required|date|before_or_equal:today',
            'vendor_id' => ['required', \Illuminate\Validation\Rule::exists('users', 'id')->where('created_by', creatorId())->whereIn('type', ['vendor'])],
            'bank_account_id' => ['required', \Illuminate\Validation\Rule::exists('bank_accounts', 'id')->where('created_by', creatorId())->where('is_active', true)],
            'reference_number' => 'nullable|string|max:100',
            'payment_amount' => 'required|numeric|min:0',
            'wht_base_amount' => 'nullable|numeric|min:0',
            'wht_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => ['required', 'distinct', \Illuminate\Validation\Rule::exists('purchase_invoices', 'id')->where('created_by', creatorId())->where('vendor_id', $this->input('vendor_id'))->whereIn('status', ['posted','partial','overdue'])],
            'allocations.*.amount' => 'required|numeric|min:0.01',
            'debit_notes' => 'nullable|array',
            'debit_notes.*.debit_note_id' => ['required', 'distinct', \Illuminate\Validation\Rule::exists('debit_notes', 'id')->where('created_by', creatorId())->where('vendor_id', $this->input('vendor_id'))->whereIn('status', ['approved','partial'])],
            'debit_notes.*.amount' => 'required|numeric|min:0.01'
        ];
    }

    public function messages()
    {
        return [
            'payment_date.before_or_equal' => __('Payment date cannot be in the future.'),
            'allocations.*.amount.min' => __('Allocation amount must be greater than 0.'),
            'debit_notes.*.amount.min' => __('Debit note amount must be greater than 0.')
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;
            $allocated = round((float) collect($this->input('allocations', []))->sum('amount'), 2);
            $notes = round((float) collect($this->input('debit_notes', []))->sum('amount'), 2);
            $wht = round((float) $this->input('wht_base_amount',0) * (float) $this->input('wht_rate',0) / 100,2);
            if ((float) $this->input('wht_base_amount',0) > $allocated) $validator->errors()->add('wht_base_amount', __('Withholding base exceeds allocations.'));
            if (abs($allocated - $notes - $wht - (float) $this->input('payment_amount')) > 0.01) {
                $validator->errors()->add('payment_amount', __('Cash payment plus notes must equal invoice allocations.'));
            }
            foreach ($this->input('allocations', []) as $index => $allocation) {
                $invoice = \Illuminate\Support\Facades\DB::table('purchase_invoices')->find($allocation['invoice_id']);
                if ((float) $allocation['amount'] > (float) $invoice->balance_amount) $validator->errors()->add("allocations.$index.amount", __('Amount exceeds invoice balance.'));
            }

            $allocations = $this->input('allocations', []);
            $debitNotes = $this->input('debit_notes', []);

            if (empty($allocations) && empty($debitNotes)) {
                $validator->errors()->add('allocations', 'At least one invoice allocation or debit note is required.');
            }
        });
    }
}

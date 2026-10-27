<?php

namespace Workdo\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreCustomerPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create-customer-payments');
    }

    public function rules()
    {
        return [
            'payment_date' => 'required|date|before_or_equal:today',
            'customer_id' => ['required', \Illuminate\Validation\Rule::exists('users', 'id')->where('created_by', creatorId())->whereIn('type', ['client', 'customer'])],
            'bank_account_id' => ['required', \Illuminate\Validation\Rule::exists('bank_accounts', 'id')->where('created_by', creatorId())->where('is_active', true)],
            'reference_number' => 'nullable|string|max:100',
            'payment_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => ['required', 'distinct', \Illuminate\Validation\Rule::exists('sales_invoices', 'id')->where('created_by', creatorId())->where('customer_id', $this->input('customer_id'))->whereIn('status', ['posted','partial','overdue'])],
            'allocations.*.amount' => 'required|numeric|min:0.01',
            'credit_notes' => 'nullable|array',
            'credit_notes.*.credit_note_id' => ['required', 'distinct', \Illuminate\Validation\Rule::exists('credit_notes', 'id')->where('created_by', creatorId())->where('customer_id', $this->input('customer_id'))->whereIn('status', ['approved','partial'])],
            'credit_notes.*.amount' => 'required|numeric|min:0.01'
        ];
    }

    public function messages()
    {
        return [
            'payment_date.before_or_equal' => __('Payment date cannot be in the future.'),
            'allocations.*.amount.min' => __('Allocation amount must be greater than 0.'),
            'credit_notes.*.amount.min' => __('Credit note amount must be greater than 0.')
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) return;
            $allocated = round((float) collect($this->input('allocations', []))->sum('amount'), 2);
            $notes = round((float) collect($this->input('credit_notes', []))->sum('amount'), 2);
            if (abs($allocated - $notes - (float) $this->input('payment_amount')) > 0.01) {
                $validator->errors()->add('payment_amount', __('Cash payment plus notes must equal invoice allocations.'));
            }
            foreach ($this->input('allocations', []) as $index => $allocation) {
                $invoice = \Illuminate\Support\Facades\DB::table('sales_invoices')->find($allocation['invoice_id']);
                if ((float) $allocation['amount'] > (float) $invoice->balance_amount) $validator->errors()->add("allocations.$index.amount", __('Amount exceeds invoice balance.'));
            }

            $allocations = $this->input('allocations', []);
            $creditNotes = $this->input('credit_notes', []);
            
            if (empty($allocations) && empty($creditNotes)) {
                $validator->errors()->add('allocations', __('At least one invoice allocation or credit note is required.'));
            }
        });
    }
}
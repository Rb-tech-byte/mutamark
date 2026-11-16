<?php

namespace Workdo\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_method' => 'nullable|in:cash,bank_transfer,mobile_money,card,cheque',
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx|max:5120',
        ];
    }
}

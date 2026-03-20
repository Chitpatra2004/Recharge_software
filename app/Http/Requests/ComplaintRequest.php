<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recharge_transaction_id' => ['sometimes', 'nullable', 'integer', 'exists:recharge_transactions,id'],
            'subject'                 => ['required', 'string', 'max:255'],
            'description'             => ['required', 'string', 'max:5000'],
            'type'                    => ['required', 'in:recharge_failed,balance_deducted,refund,other'],
        ];
    }
}

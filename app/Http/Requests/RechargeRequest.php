<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RechargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'mobile'          => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            // FIX L2: validate operator_code against the DB — fails fast before
            // the service layer attempts any wallet lock or route lookup.
            'operator_code'   => ['required', 'string', 'max:30', 'exists:operators,code'],
            'amount'          => [
                'required', 'numeric',
                'min:'  . config('recharge.min_amount', 10),
                'max:'  . config('recharge.max_amount', 10000),
            ],
            'recharge_type'   => ['sometimes', 'in:prepaid,postpaid,dth,broadband'],
            'circle'          => ['sometimes', 'nullable', 'string', 'max:50'],
            // FIX H2: buyer_id removed — it must not be user-controlled input.
            // The service layer resolves the buyer from the authenticated API key.
            // 'buyer_id' => ['sometimes', 'nullable', 'integer', 'exists:buyers,id'],
            // Client MUST generate a UUID per request — enforced server-side too
            'idempotency_key' => ['required', 'string', 'min:16', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.regex' => 'Mobile number must be a valid 10-digit Indian mobile number.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'operator_code' => strtoupper(trim($this->operator_code ?? '')),
            'mobile'        => preg_replace('/\D/', '', $this->mobile ?? ''),
        ]);
    }
}

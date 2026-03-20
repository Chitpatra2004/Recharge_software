<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletTopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:1000000'],
            'description' => ['required', 'string', 'max:255'],
        ];
    }
}

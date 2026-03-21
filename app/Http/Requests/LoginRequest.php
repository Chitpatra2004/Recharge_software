<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Accept either email or mobile (10-digit number)
            'login'       => ['required', 'string', 'max:255'],
            'password'    => ['required', 'string', 'min:1'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required'    => 'Email or mobile number is required.',
            'password.required' => 'Password is required.',
        ];
    }

    /** Resolve whether login is email or mobile */
    public function loginField(): string
    {
        return filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
    }
}

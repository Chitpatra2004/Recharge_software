<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'min:2', 'max:100'],
            'email'       => ['required', 'email', 'max:150', 'unique:users,email'],
            'mobile'      => ['required', 'digits:10', 'unique:users,mobile'],
            'password'    => [
                'required', 'string', 'confirmed', 'max:72',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'role'        => ['sometimes', 'in:retailer,distributor,api_user,buyer'],
            'device_name' => ['sometimes', 'string', 'max:100'],
            'document'    => [
                'sometimes', 'nullable', 'file',
                'mimes:jpg,jpeg,png,pdf,webp',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'      => 'This email address is already registered.',
            'mobile.unique'     => 'This mobile number is already registered.',
            'mobile.digits'     => 'Mobile number must be exactly 10 digits.',
            'password.confirmed'=> 'Password confirmation does not match.',
            'password.min'      => 'Password must be at least 8 characters.',
            'document.mimes'    => 'Document must be JPG, PNG, PDF, or WebP.',
            'document.max'      => 'Document must not exceed 4 MB.',
        ];
    }
}

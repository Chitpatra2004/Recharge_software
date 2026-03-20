<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'password'    => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'role'        => ['sometimes', 'in:retailer,distributor,api_user'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'    => 'This email address is already registered.',
            'mobile.unique'   => 'This mobile number is already registered.',
            'mobile.digits'   => 'Mobile number must be exactly 10 digits.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}

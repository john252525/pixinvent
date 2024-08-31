<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', 'max:254', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'remember_token' => ['nullable'],
            'privacyPolicies' => ['accepted'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

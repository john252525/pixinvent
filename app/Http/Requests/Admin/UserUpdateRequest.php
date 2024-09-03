<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', 'max:254', 'unique:users,email,'.$this->user->id],
            'email_verified_at' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['array', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

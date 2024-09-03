<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$this->route('role')->id],
            'permissions' => ['required', 'array', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

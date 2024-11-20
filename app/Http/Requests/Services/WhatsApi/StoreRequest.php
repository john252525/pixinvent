<?php

namespace App\Http\Requests\Services\WhatsApi;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['string', 'max:200'],
            'base' => ['required_without:file_base', 'string'],
            'file_base' => ['required_without:base', 'file', 'mimes:xlsx,xls,csv,txt'],
            'ph_col' => ['nullable', 'string', 'max:1'],
            'text' => ['required', 'string'],
            'file_attach' => ['file', 'mimes:jpeg,jpg,gif,png,pdf,doc,docx,xls,xlsx'],
            'days' => ['string', 'regex:/^\d+(,\d+)*$/'],
            'days.*' => ['int', 'between:1,7'],
            'time_from' => ['date_format:H:i'],
            'time_to' => ['date_format:H:i'],
            'timezone' => ['string'],
            'delay_from' => ['numeric'],
            'delay_to' => ['numeric'],
            'uniq' => ['bool'],
            'exist' => ['bool'],
            'random' => ['bool'],
            'cascade' => ['string', 'regex:/^(\w+)(,\w+)*$/'],
            'cascade.*' => ['string', 'in:telegram,whatsapp,sms'],
        ];
    }

    public function attributes(): array
    {
        return [
            'days.*' => 'days',
            'cascade.*' => 'cascade',
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('days.*')) {
                $errors = $validator->errors()->get('days.*');
                foreach ($errors as $messages) {
                    $validator->errors()->add('days', implode(', ', $messages));
                }
                $validator->errors()->forget('days.*');
            }

            if ($validator->errors()->has('cascade.*')) {
                $errors = $validator->errors()->get('cascade.*');
                foreach ($errors as $messages) {
                    $validator->errors()->add('cascade', implode(', ', $messages));
                }
                $validator->errors()->forget('cascade.*');
            }
        });
    }
}

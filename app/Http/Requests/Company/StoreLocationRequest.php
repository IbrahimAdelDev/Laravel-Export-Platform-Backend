<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city'    => ['required', 'string', 'max:100'], // مثلا: الفرع الرئيسي، مخزن دمياط
            'address' => ['required', 'string', 'max:255'],
            'type'    => ['required', 'string', 'in:headquarter,branch,warehouse'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
        ];
    }
}
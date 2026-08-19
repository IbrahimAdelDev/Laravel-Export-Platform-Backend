<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class StorePhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20', 'unique:phones,phone'],
            'type'   => ['required', 'string', 'in:mobile,landline,fax,whatsapp'],
        ];
    }
}
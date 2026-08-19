<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'type'        => ['sometimes', 'string', 'in:individual,company'],
            'email'       => ['sometimes', 'string', 'email', 'max:255'],
            'website'     => ['sometimes', 'string', 'max:255'],
            'extra_details' => ['sometimes', 'string', 'max:1000'],
            'status'      => ['sometimes', 'string', 'in:active,inactive'],
            'country_id'  => ['sometimes', 'integer', 'exists:countries,id'],
        ];
    }
}
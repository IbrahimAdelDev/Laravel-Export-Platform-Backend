<?php

namespace App\Http\Requests\Directory;

use Illuminate\Foundation\Http\FormRequest;

class GetImportersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'type'       => ['nullable', 'string', 'in:importer,both'],
            'hs_code'    => ['nullable', 'string', 'exists:products,hs_code_6'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
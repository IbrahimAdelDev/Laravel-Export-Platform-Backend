<?php

namespace App\Http\Requests\Exporter;

use Illuminate\Foundation\Http\FormRequest;

class GetTopImportersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page'     => ['nullable', 'integer', 'min:1'],
        ];
    }
}
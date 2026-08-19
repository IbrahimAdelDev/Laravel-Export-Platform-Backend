<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GetAdminCompaniesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'     => ['nullable', 'string', 'in:importer,exporter,both'],
            'status'   => ['nullable', 'string', 'in:pending,verified,rejected'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
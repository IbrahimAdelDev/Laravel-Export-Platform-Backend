<?php

namespace App\Http\Requests\Admin\DataImport;

use Illuminate\Foundation\Http\FormRequest;

class PreviewImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:51200',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload an Excel file.',
            'file.mimes' => 'The file must be in Excel format (xlsx or xls).',
        ];
    }
}
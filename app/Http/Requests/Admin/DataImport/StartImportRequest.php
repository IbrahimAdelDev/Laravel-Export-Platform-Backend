<?php

namespace App\Http\Requests\Admin\DataImport;

use Illuminate\Foundation\Http\FormRequest;

class StartImportRequest extends FormRequest
{
    public function authorize(): bool { 
        return true; 
    }

    public function rules(): array
    {
        return [
            'file_path'         => 'required|string',
            'import_type'       => 'required|string',
            'origin_country_id' => 'required|exists:countries,id',
            'sheets_mapping'    => 'required|array|min:1',
            'sheets_mapping.*.sheet_name' => 'required|string', // مثال: "Sheet 1"
            'sheets_mapping.*.columns'    => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'file_path.required' => 'Please provide the file path.',
            'import_type.required' => 'Please specify the import type.',
            'origin_country_id.required' => 'Please select the origin country.',
            'origin_country_id.exists' => 'The selected origin country is invalid.',
            'sheets_mapping.required' => 'Please provide the sheets mapping.',
            'sheets_mapping.min' => 'Please provide at least one sheet mapping.',
            'sheets_mapping.*.sheet_name.required' => 'Please provide the sheet name.',
            'sheets_mapping.*.columns.required' => 'Please provide the columns for each sheet.',
        ];
    }
}
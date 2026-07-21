<?php

namespace App\Http\Requests\Exporter;

use Illuminate\Foundation\Http\FormRequest;

class GetDemandTrendsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'year' => [
                'nullable', 
                'integer', 
                'min:2010', 
                'max:' . date('Y')
            ],
        ];
    }
}
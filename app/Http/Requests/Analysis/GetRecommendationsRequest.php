<?php

namespace App\Http\Requests\Analysis;

use Illuminate\Foundation\Http\FormRequest;

class GetRecommendationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'hs_code'  => ['required', 'string', 'exists:products,hs_code_6'],
            'quantity' => ['nullable', 'numeric', 'min:1'], 
        ];
    }

    public function messages(): array
    {
        return [
            'hs_code.exists' => __('The provided HS code does not exist in our database.'),
        ];
    }
}
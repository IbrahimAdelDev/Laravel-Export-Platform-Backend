<?php

namespace App\Http\Requests\Company;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 1. company data
            'company_name'        => 'required|string|max:255',
            'country_id'          => 'required|integer|exists:countries,id',
            'type'                => 'required|in:exporter,importer,both',
            'email'               => 'nullable|email|max:255',
            'website'             => 'nullable|url|max:255',
            // 'extra_details'       => 'nullable|array', // allows for any additional details as an array
            
            // 2. phone data (polymorphic relationship)
            'phones'              => 'required|array|min:1',
            'phones.*.phone'      => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|max:20', // basic phone number validation (allows digits, spaces, dashes, parentheses, and plus sign)
            'phones.*.label'      => 'nullable|string|max:50', // 'Sales', 'WhatsApp', 'Main'

            // 3. location data
            'location'            => 'required|array',
            'location.country_id' => 'required|integer|exists:countries,id',
            'location.address'    => 'required|string|max:500',
            'location.city'    => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.exists' => 'The selected country does not exist.',
            'type.in' => 'The type must be one of: exporter, importer, both.',
            'phones.*.number.regex' => 'The phone number format is invalid.',
            'phones.*.number.unique' => 'The phone number is already in use.',
            'location.country_id.exists' => 'The selected location country does not exist.',
            'location.city.string' => 'The city must be a string.',
        ];
    }
}

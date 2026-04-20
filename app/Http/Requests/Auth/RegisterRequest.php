<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            // 1. user data
            'user_name'         => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:8|confirmed', // password_confirmation is required for the confirmed rule

            // 2. phone data (polymorphic relationship)
            'phones'            => 'required|array|min:1',
            'phones.*.phone'   => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|max:20', // basic phone number validation (allows digits, spaces, dashes, parentheses, and plus sign)
            'phones.*.label'    => 'nullable|string|max:50', // 'Sales', 'WhatsApp', 'Main'
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phones.*.number.regex' => 'The phone number format is invalid.',
            'phones.*.number.unique' => 'The phone number is already in use.',
        ];
    }
}

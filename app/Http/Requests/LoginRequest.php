<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|min:10|regex:/^\+?[1-9]\d{1,14}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Le numéro de téléphone est requis.',
            'phone_number.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone_number.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
            'phone_number.regex' => 'Le numéro de téléphone doit être au format international (ex: +22145678901).',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_pin' => 'required|string|size:4|regex:/^\d{4}$/',
            'new_pin' => 'required|string|size:4|regex:/^\d{4}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'old_pin.required' => 'L\'ancien PIN est requis.',
            'old_pin.size' => 'L\'ancien PIN doit contenir exactement 4 chiffres.',
            'old_pin.regex' => 'L\'ancien PIN doit contenir uniquement des chiffres.',
            'new_pin.required' => 'Le nouveau PIN est requis.',
            'new_pin.size' => 'Le nouveau PIN doit contenir exactement 4 chiffres.',
            'new_pin.regex' => 'Le nouveau PIN doit contenir uniquement des chiffres.',
        ];
    }
}

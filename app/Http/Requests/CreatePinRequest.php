<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => 'required|string|size:4|regex:/^\d{4}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'Le PIN est requis.',
            'pin.size' => 'Le PIN doit contenir exactement 4 chiffres.',
            'pin.regex' => 'Le PIN doit contenir uniquement des chiffres.',
        ];
    }
}

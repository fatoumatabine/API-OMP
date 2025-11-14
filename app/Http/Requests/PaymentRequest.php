<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:100|max:10000000',
            'merchant_identifier' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'pin' => 'nullable|string|size:4|regex:/^\d{4}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant est requis.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant minimum doit être de 100 XOF.',
            'amount.max' => 'Le montant maximum doit être de 10 000 000 XOF.',
            'merchant_identifier.required' => 'L\'identifiant du marchand est requis.',
            'merchant_identifier.max' => 'L\'identifiant du marchand ne doit pas dépasser 50 caractères.',
            'description.max' => 'La description ne doit pas dépasser 255 caractères.',
            'pin.size' => 'Le PIN doit contenir exactement 4 chiffres.',
            'pin.regex' => 'Le PIN doit contenir uniquement des chiffres.',
        ];
    }
}

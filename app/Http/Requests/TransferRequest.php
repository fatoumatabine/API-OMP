<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_phone' => 'required|string|min:10|regex:/^\+?[1-9]\d{1,14}$/',
            'amount' => 'required|numeric|min:100|max:10000000',
            'description' => 'nullable|string|max:255',
            'pin' => 'nullable|string|size:4|regex:/^\d{4}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_phone.required' => 'Le numéro du destinataire est requis.',
            'receiver_phone.regex' => 'Le numéro de téléphone doit être au format international.',
            'amount.required' => 'Le montant est requis.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant minimum doit être de 100 XOF.',
            'amount.max' => 'Le montant maximum doit être de 10 000 000 XOF.',
            'description.max' => 'La description ne doit pas dépasser 255 caractères.',
            'pin.size' => 'Le PIN doit contenir exactement 4 chiffres.',
            'pin.regex' => 'Le PIN doit contenir uniquement des chiffres.',
        ];
    }
}

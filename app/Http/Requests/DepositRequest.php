<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:100|max:10000000',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Le montant est requis.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant minimum doit être de 100 XOF.',
            'amount.max' => 'Le montant maximum doit être de 10 000 000 XOF.',
        ];
    }
}

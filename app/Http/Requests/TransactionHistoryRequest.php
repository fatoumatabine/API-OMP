<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Le numéro de page doit être un nombre.',
            'page.min' => 'Le numéro de page doit être au minimum 1.',
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un nombre.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au minimum 1.',
            'per_page.max' => 'Le nombre d\'éléments par page ne doit pas dépasser 100.',
        ];
    }
}

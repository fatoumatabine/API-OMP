<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'unique:users', new PhoneNumber],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'pin_code' => 'required|string|min:4|max:4',
            'cni_number' => 'required|string|unique:users|max:255',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Le numéro de téléphone est obligatoire.',
            'phone_number.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'pin_code.required' => 'Le code PIN est obligatoire.',
            'pin_code.min' => 'Le code PIN doit contenir 4 chiffres.',
            'pin_code.max' => 'Le code PIN doit contenir 4 chiffres.',
            'cni_number.required' => 'Le numéro CNI est obligatoire.',
            'cni_number.unique' => 'Ce numéro CNI est déjà utilisé.',
        ];
    }
}

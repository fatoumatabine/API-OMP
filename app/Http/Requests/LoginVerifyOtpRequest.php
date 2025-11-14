<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginVerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|min:10|regex:/^\+?[1-9]\d{1,14}$/',
            'otp' => 'required|string|size:6|regex:/^\d{6}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Le numéro de téléphone est requis.',
            'phone_number.regex' => 'Le numéro de téléphone doit être au format international.',
            'otp.required' => 'Le code OTP est requis.',
            'otp.size' => 'Le code OTP doit contenir exactement 6 chiffres.',
            'otp.regex' => 'Le code OTP doit contenir uniquement des chiffres.',
        ];
    }
}

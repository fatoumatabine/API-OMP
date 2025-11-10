<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Expression régulière pour valider un numéro de téléphone au format international
        // Exemple: +22245678901 (pour la Mauritanie) ou 0022245678901
        // Cette regex est un exemple et peut être ajustée selon les besoins spécifiques.
        // Elle accepte les numéros commençant par + ou 00, suivis de chiffres.
        if (!preg_match('/^(\+|00)[1-9][0-9]{7,14}$/', $value)) {
            $fail('Le :attribute doit être un numéro de téléphone valide au format international (ex: +22245678901).');
        }
    }
}

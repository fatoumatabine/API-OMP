# Quick Start - Nouvelles Améliorations OMPAY

## 🎯 Changements Rapides à Connaître

### 1. FormRequest Classes
**Avant:**
```php
public function login(Request $request)
{
    $request->validate([
        'phone_number' => 'required|string|min:10',
    ]);
    // ...
}
```

**Après:**
```php
public function login(LoginRequest $request)
{
    // Validation automatique avec messages personnalisés
    // ...
}
```

### 2. Rate Limiting
Ajouté sur toutes les routes sensibles:
- `/api/auth/*` → 5 requêtes/minute
- `/api/wallet/*` → 20 requêtes/minute
- `/api/transactions/*` → 20 requêtes/minute

Si dépassement → **HTTP 429**
```json
{
    "success": false,
    "message": "Trop de requêtes. Veuillez réessayer plus tard.",
    "retry_after": 45
}
```

### 3. Validations Améliorées
Tous les endpoints ont maintenant:
- ✅ Validation regex pour les numéros de téléphone
- ✅ Messages d'erreur en français
- ✅ Limites claires pour amounts
- ✅ Validation des formats (OTP 6 chiffres, PIN 4 chiffres)

---

## 📋 Fichiers Importants

### Nouvelles FormRequest Classes
```
app/Http/Requests/
├── LoginRequest.php
├── VerifyOtpRequest.php
├── ChangePinRequest.php
├── CreatePinRequest.php
├── ResendOtpRequest.php
├── DepositRequest.php
├── TransferRequest.php
├── PaymentRequest.php
└── TransactionHistoryRequest.php
```

### Middleware
```
app/Http/Middleware/RateLimitMiddleware.php
```

### Contrôleurs Modifiés
```
app/Http/Controllers/
├── AuthController.php ✏️
├── WalletController.php ✏️
└── TransactionController.php ✏️
```

---

## 🚀 Ajouter une Nouvelle Validation

### Exemple: Ajouter une validation pour un nouvel endpoint

1. **Créer une FormRequest class:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class YourNewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:100',
        ];
    }

    public function messages(): array
    {
        return [
            'field_name.required' => 'Le champ est requis.',
            'amount.min' => 'Le montant minimum est 100 XOF.',
        ];
    }
}
```

2. **Utiliser dans le controller:**
```php
public function yourMethod(YourNewRequest $request)
{
    $validated = $request->validated();
    // ... logique métier
}
```

3. **Ajouter le middleware rate limiting aux routes:**
```php
Route::post('your-endpoint', [YourController::class, 'yourMethod'])
    ->middleware(['auth:api', 'rate.limit']);
```

---

## 🔍 Tester les Validations

### Test 1: Invalid Phone Number
```bash
curl -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone_number": "invalid"}'
```

**Réponse attendue:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "phone_number": [
            "Le numéro de téléphone doit être au format international (ex: +22145678901)."
        ]
    }
}
```

### Test 2: Invalid OTP (Not 6 digits)
```bash
curl -X POST http://localhost:8001/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone_number": "+22145678901", "otp": "123"}'
```

**Réponse attendue:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "otp": [
            "Le code OTP doit contenir exactement 6 chiffres."
        ]
    }
}
```

### Test 3: Rate Limiting (Send 6 requests in 1 minute)
```bash
for i in {1..6}; do
  curl -X POST http://localhost:8001/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{"phone_number": "+22145678901"}' \
    && echo "\nRequest $i"
done
```

**La 6e requête retournera HTTP 429:**
```json
{
    "success": false,
    "message": "Trop de requêtes. Veuillez réessayer plus tard.",
    "retry_after": 45
}
```

---

## 🛠️ Commandes Utiles

### Vérifier la syntaxe PHP
```bash
php -l app/Http/Controllers/*.php
php -l app/Http/Requests/*.php
```

### Exécuter les tests
```bash
php artisan test
```

### Lister les routes
```bash
php artisan route:list | grep api
```

### Régénérer la documentation Swagger
```bash
php artisan l5-swagger:generate
```

### Accéder à la documentation
```
http://localhost:8001/api/documentation
```

---

## 📊 Regex Patterns Utilisés

### Phone Number (International Format)
```regex
^\+?[1-9]\d{1,14}$
```
✅ Exemples valides:
- `+22145678901` (Sénégal)
- `+22245678901` (Mauritanie)
- `+33612345678` (France)
- `+1234567890` (USA)

### OTP (6 Digits)
```regex
^\d{6}$
```
✅ Valides: `123456`, `000000`
❌ Invalides: `12345`, `1234567`

### PIN (4 Digits)
```regex
^\d{4}$
```
✅ Valides: `1234`, `0000`, `9999`
❌ Invalides: `123`, `12345`

---

## 🔐 Configuration Rate Limiting

Fichier: `app/Http/Middleware/RateLimitMiddleware.php`

Modifier les limites:
```php
protected function getLimit(Request $request): int
{
    return match (true) {
        $request->is('api/auth/*') => 10,  // Augmenter à 10
        $request->is('api/transactions/*') => 30,  // Augmenter à 30
        // ... autres routes
    };
}
```

---

## 🚨 Erreurs Communes

### Error: "Type 'Request' not found"
**Cause**: Import manquant
**Solution**: Ajouter `use Illuminate\Http\Request;`

### Error: Swagger not generating
**Solution**: Exécuter `php artisan l5-swagger:generate`

### Validation not working
**Check**: Assurez-vous que la FormRequest class est utilisée
```php
// ❌ Mauvais
public function login(Request $request)

// ✅ Correct
public function login(LoginRequest $request)
```

---

## 📈 Prochaines Améliorations

- [ ] Tests unitaires pour FormRequest classes
- [ ] Tests d'intégration pour rate limiting
- [ ] Caching pour performance
- [ ] Audit logging pour transactions
- [ ] WebSocket pour notifications
- [ ] GraphQL API
- [ ] API versioning

---

## 📞 Support

Pour questions ou problèmes:
1. Vérifier IMPROVEMENTS.md pour documentation complète
2. Vérifier CHANGES_SUMMARY.md pour résumé détaillé
3. Consulter les tests existants: `tests/`

---

**Version**: 1.0.1
**Date**: 13 novembre 2025
**Status**: ✅ Production Ready

# OMPAY API Documentation

## Vue d'ensemble

OMPAY est une plateforme de transfert d'argent sécurisée permettant les utilisateurs de :
- Créer un compte et gérer leur profil
- Authentifier avec JWT tokens
- Déposer et retirer de l'argent
- Transférer de l'argent à d'autres utilisateurs
- Consulter l'historique des transactions

**Base URL:** `http://127.0.0.1:8001/api`

**Version:** 1.0.0

## Authentification

L'API utilise **JWT (JSON Web Tokens)** pour l'authentification.

### Obtenir un token

1. **Créer un compte** (POST `/register`)
2. **Se connecter** (POST `/auth/login`) pour obtenir un JWT token
3. **Inclure le token** dans le header `Authorization: Bearer <token>` pour les requêtes protégées

### Exemple d'utilisation du token

```bash
curl -X GET http://127.0.0.1:8001/api/wallet/balance \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

## Format des réponses

Toutes les réponses suivent ce format standardisé :

```json
{
  "success": true,
  "message": "Description de la réponse",
  "data": {
    // Données spécifiques à l'endpoint
  },
  "errors": null
}
```

### Codes HTTP

| Code | Signification |
|------|--------------|
| 200 | OK - Requête réussie |
| 201 | Created - Ressource créée |
| 400 | Bad Request - Erreur de validation |
| 401 | Unauthorized - Non authentifié ou token expiré |
| 404 | Not Found - Ressource non trouvée |
| 422 | Unprocessable Entity - Erreurs de validation détaillées |
| 500 | Internal Server Error - Erreur serveur |

## Endpoints

### Authentication

#### 1. Enregistrement (Registration)

```http
POST /api/register
Content-Type: application/json

{
  "phone_number": "+22145678901",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "pin_code": "1234",
  "cni_number": "1234567890ABC"
}
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Utilisateur enregistré avec succès.",
  "data": {
    "id": "a056f3b0-563d-4c14-ae26-24cdf0ba0a4e",
    "phone_number": "+22145678901",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "kyc_status": "pending",
    "biometrics_active": false,
    "balance": "0.00",
    "created_at": "2025-11-12T13:12:40.000000Z",
    "updated_at": "2025-11-12T13:12:40.000000Z"
  }
}
```

#### 2. Connexion (Login)

```http
POST /api/auth/login
Content-Type: application/json

{
  "phone_number": "+22145678901",
  "password": "password123"
}
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": "a056f3b0-563d-4c14-ae26-24cdf0ba0a4e",
      "phone_number": "+22145678901",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "balance": "0.00",
      "kyc_status": "pending",
      "biometrics_active": false,
      "created_at": "2025-11-12T13:12:40.000000Z",
      "updated_at": "2025-11-12T13:12:40.000000Z"
    }
  }
}
```

#### 3. Vérification OTP

```http
POST /api/auth/verify-otp
Content-Type: application/json

{
  "phone_number": "+22145678901",
  "otp": "123456"
}
```

> **Note:** Le code OTP valide pour le test est `123456`

#### 4. Renvoyer OTP

```http
POST /api/auth/resend-otp
Content-Type: application/json

{
  "phone_number": "+22145678901"
}
```

#### 5. Créer un code PIN

```http
POST /api/auth/create-pin
Authorization: Bearer <token>
Content-Type: application/json

{
  "pin": "5678"
}
```

> **Note:** Le PIN est créé automatiquement lors de l'inscription avec le champ `pin_code`

#### 6. Changer le code PIN

```http
POST /api/auth/change-pin
Authorization: Bearer <token>
Content-Type: application/json

{
  "old_pin": "1234",
  "new_pin": "5678"
}
```

#### 7. Rafraîchir le token

```http
POST /api/auth/refresh-token
Authorization: Bearer <token>
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Token rafraîchi avec succès",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

#### 8. Déconnexion (Logout)

```http
POST /api/auth/logout
Authorization: Bearer <token>
```

---

### Wallet (Portefeuille)

#### 1. Obtenir le solde

```http
GET /api/wallet/balance
Authorization: Bearer <token>
```

**Réponse (200):**
```json
{
  "balance": "10000.50",
  "currency": "XOF"
}
```

#### 2. Effectuer un dépôt

```http
POST /api/wallet/deposit
Authorization: Bearer <token>
Content-Type: application/json

{
  "amount": 10000,
  "method": "card"
}
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Dépôt effectué avec succès",
  "data": {
    "new_balance": 10000,
    "transaction": {
      "id": "a056f3b6-b25a-4b8f-bb01-6213bfd35ff6",
      "type": "deposit",
      "amount": "10000.00",
      "fees": "0.00",
      "status": "completed",
      "reference": "DEP1762953164",
      "description": "Dépôt d'argent",
      "created_at": "2025-11-12T13:12:44.000000Z"
    }
  }
}
```

---

### Transactions

#### 1. Effectuer un transfert

```http
POST /api/transactions/transfer
Authorization: Bearer <token>
Content-Type: application/json

{
  "receiver_phone": "+22178901234",
  "amount": 5000,
  "description": "Remboursement",
  "pin": "1234"
}
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Transfert effectué avec succès",
  "data": {
    "transaction": {
      "id": "a056f3b6-b25a-4b8f-bb01-6213bfd35ff6",
      "sender_id": "a056f3b0-563d-4c14-ae26-24cdf0ba0a4e",
      "receiver_id": "a056f4ab-c6c4-407f-8351-9a1f9959752e",
      "amount": "5000.00",
      "fees": "50.00",
      "type": "transfer",
      "status": "completed",
      "reference": "TRF1762953400",
      "description": "Remboursement",
      "created_at": "2025-11-12T13:15:40.000000Z"
    },
    "new_balance": 4950
  }
}
```

#### 2. Obtenir l'historique des transactions

```http
GET /api/transactions/history?page=1&per_page=10
Authorization: Bearer <token>
```

**Réponse (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": "a056f3b6-b25a-4b8f-bb01-6213bfd35ff6",
      "sender_id": null,
      "receiver_id": "a056f3b0-563d-4c14-ae26-24cdf0ba0a4e",
      "amount": "10000.00",
      "fees": "0.00",
      "type": "deposit",
      "status": "completed",
      "reference": "DEP1762953164",
      "description": "Dépôt d'argent",
      "created_at": "2025-11-12T13:12:44.000000Z",
      "updated_at": "2025-11-12T13:12:44.000000Z",
      "sender": null,
      "receiver": {
        "id": "a056f3b0-563d-4c14-ae26-24cdf0ba0a4e",
        "phone_number": "+22145678901",
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com"
      }
    }
  ],
  "first_page_url": "http://127.0.0.1:8001/api/transactions/history?page=1",
  "last_page": 1,
  "per_page": 10,
  "total": 1
}
```

---

## Erreurs courantes

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Identifiants invalides"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Erreurs de validation",
  "errors": {
    "phone_number": [
      "Le phone number doit être un numéro de téléphone valide au format international (ex: +22245678901)."
    ],
    "email": [
      "L'email doit être une adresse email valide."
    ]
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Destinataire non trouvé"
}
```

---

## Format du numéro de téléphone

Tous les numéros de téléphone doivent être au format international :
- **Sénégal (CIO +221):** `+22145678901`
- **Mauritanie (CIO +222):** `+22245678901`
- Autres pays: Utiliser le code pays complet (ex: `+33612345678` pour la France)

---

## Limites et restrictions

| Paramètre | Valeur |
|-----------|--------|
| Montant minimum | 100 XOF |
| PIN Code | 4 chiffres |
| Password | Min 8 caractères |
| Rate limit | À définir |
| Token expiration | À définir |

---

## Tester l'API

### Avec cURL

```bash
# 1. Enregistrement
curl -X POST http://127.0.0.1:8001/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145678901",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "pin_code": "1234",
    "cni_number": "1234567890ABC"
  }'

# 2. Connexion
curl -X POST http://127.0.0.1:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145678901",
    "password": "password123"
  }'

# 3. Obtenir le solde
curl -X GET http://127.0.0.1:8001/api/wallet/balance \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Avec le script de test

```bash
# Exécuter tous les tests
bash test_all_api.sh

# Tester uniquement l'inscription
bash test_register.sh
```

### Avec Postman

1. Importer la collection Postman
2. Remplacer `{{base_url}}` par `http://127.0.0.1:8001`
3. Utiliser la variable `{{token}}` pour les endpoints protégés

---

## Support

Pour toute question ou problème, contactez : support@ompay.com

---

**Dernière mise à jour:** 12 novembre 2025
**Version API:** 1.0.0

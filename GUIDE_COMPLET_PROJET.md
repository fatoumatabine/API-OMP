# 📱 Guide Complet du Projet OMPAY

**Version:** 1.0.0  
**Date:** Novembre 2025  
**Langue:** Français  

---

## 📋 Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture technique](#architecture-technique)
3. [Technologies utilisées](#technologies-utilisées)
4. [Structure du projet](#structure-du-projet)
5. [Modèles de données](#modèles-de-données)
6. [API - Routes et endpoints](#api---routes-et-endpoints)
7. [Processus clés](#processus-clés)
8. [Installation et configuration](#installation-et-configuration)
9. [Déploiement](#déploiement)
10. [Guide de développement](#guide-de-développement)

---

## 🎯 Vue d'ensemble

**OMPAY** est une plateforme de paiement mobile et de portefeuille numérique construite avec **Laravel 10**.

### Fonctionnalités principales

- ✅ Authentification sécurisée avec JWT et OTP
- ✅ Gestion de portefeuille (Wallet) multi-devise
- ✅ Transferts d'argent entre utilisateurs
- ✅ Historique des transactions
- ✅ Gestion des codes PIN pour les transactions sensibles
- ✅ Vérification KYC (Know Your Customer)
- ✅ Codes QR pour les transactions
- ✅ Support biométrique
- ✅ Logging et audit complets
- ✅ Documentation API automatique (Swagger/OpenAPI)

### Cas d'usage

OMPAY est conçu pour:
- Permettre les transferts d'argent rapides et sécurisés
- Gérer un portefeuille numérique
- Effectuer des paiements marchands
- Consulter l'historique des transactions
- Sécuriser les transactions sensibles avec PIN et OTP

---

## 🏗️ Architecture technique

### Architecture générale

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT (Mobile/Web)                       │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTP(S)
                         │
        ┌────────────────▼────────────────┐
        │   Laravel 10 API Backend        │
        │   - Routing                     │
        │   - Authentication (JWT)        │
        │   - Controllers                 │
        │   - Business Logic              │
        └────────────────┬────────────────┘
                         │
        ┌────────────────▼────────────────┐
        │   Eloquent ORM                  │
        │   - Models                      │
        │   - Relationships               │
        │   - Migrations                  │
        └────────────────┬────────────────┘
                         │
        ┌────────────────▼────────────────┐
        │   SQLite / MySQL Database       │
        │   - Users                       │
        │   - Wallets                     │
        │   - Transactions                │
        │   - Audit Logs                  │
        └─────────────────────────────────┘
```

### Flux d'authentification

```
1. Utilisateur s'enregistre
   └─> POST /api/register
       └─> Création User + Wallet
           └─> Email OTP envoyé

2. Utilisateur se connecte
   └─> POST /api/auth/login
       └─> OTP envoyé par email

3. Vérification OTP
   └─> POST /api/auth/verify-otp
       └─> JWT Token généré et retourné

4. Requêtes authentifiées
   └─> Authorization: Bearer <JWT_TOKEN>
       └─> Middleware auth:api valide le token

5. Renouvellement token
   └─> POST /api/auth/refresh-token
       └─> Nouveau token émis

6. Déconnexion
   └─> POST /api/auth/logout
       └─> Token invalidé
```

### Flux d'une transaction (Transfert)

```
1. Client envoie une demande de transfert
   └─> POST /api/transactions/transfer
       Params: receiver_phone, amount, description, pin

2. Validation
   ├─ Utilisateur authentifié? (JWT)
   ├─ PIN correct?
   ├─ Solde suffisant?
   ├─ Destinataire existe?
   └─ Montant valide?

3. Exécution
   ├─ Débiter compte expéditeur
   ├─ Créditer compte destinataire
   └─ Créer enregistrement Transaction

4. Logging
   ├─ Enregistrer transaction
   ├─ Créer audit log
   └─ Mettre à jour historique

5. Réponse
   └─> Succès avec détails transaction
```

---

## 🛠️ Technologies utilisées

### Backend
| Technologie | Version | Usage |
|-------------|---------|-------|
| PHP | 8.2+ | Langage principal |
| Laravel | 10.x | Framework web |
| Eloquent ORM | Intégré | ORM base de données |
| JWT Auth (Tymon) | 2.2 | Authentification tokens |
| L5 Swagger | 8.6 | Documentation API |
| Twilio SDK | 8.8 | SMS/OTP optionnel |

### Base de données
| BD | Usage |
|----|-------|
| SQLite | Développement local |
| MySQL | Production |

### Outils supplémentaires
- **Docker** - Conteneurisation
- **Composer** - Gestion dépendances PHP
- **NPM** - Gestion dépendances JavaScript/frontend
- **PHPUnit** - Tests unitaires
- **Postman** - Tests API

---

## 📂 Structure du projet

```
OMPAY/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Authentification, PIN, OTP
│   │   │   ├── CompteController.php        # Enregistrement, profil
│   │   │   ├── WalletController.php        # Solde, dépôts
│   │   │   ├── TransactionController.php   # Transferts, historique
│   │   │   ├── HealthController.php        # Santé de l'API
│   │   │   └── Controller.php              # Contrôleur de base
│   │   ├── Requests/                       # Form Request Validation
│   │   ├── Middleware/                     # Middleware (auth, logging, rate limit)
│   │   └── Traits/
│   │       └── ApiResponseTrait.php        # Format réponses API uniformes
│   ├── Models/
│   │   ├── User.php                        # Modèle utilisateur (JWT)
│   │   ├── Wallet.php                      # Modèle portefeuille
│   │   ├── Transaction.php                 # Modèle transactions
│   │   ├── Transfer.php                    # Modèle transferts
│   │   ├── Authentication.php              # Logs authentification
│   │   ├── History.php                     # Historique utilisateur
│   │   ├── SecuritySetting.php             # Paramètres sécurité
│   │   ├── Contact.php                     # Contacts utilisateur
│   │   ├── Merchant.php                    # Marchands
│   │   ├── Payment.php                     # Paiements
│   │   ├── PaymentCode.php                 # Codes paiement
│   │   ├── Recipient.php                   # Destinataires
│   │   └── QrCode.php                      # Codes QR
│   ├── Services/
│   │   ├── OtpService.php                  # Service OTP (génération, envoi)
│   │   ├── AuditLogService.php             # Service logging audit
│   │   └── [autres services]
│   └── Exceptions/                         # Exceptions personnalisées
├── database/
│   ├── migrations/                         # Migrations (schéma BD)
│   ├── seeders/                            # Seeders (données initiales)
│   └── factories/                          # Factories (test data)
├── routes/
│   ├── api.php                             # Routes API
│   ├── web.php                             # Routes web (si applicable)
│   └── console.php                         # Commandes CLI
├── config/
│   ├── auth.php                            # Configuration authentification
│   ├── database.php                        # Configuration BD
│   ├── jwt.php                             # Configuration JWT
│   └── [autres configs]
├── resources/
│   ├── views/                              # Templates Blade
│   └── js/                                 # Code frontend (Vite)
├── storage/
│   ├── api-docs/
│   │   └── swagger.yaml                    # Documentation OpenAPI
│   ├── logs/                               # Fichiers logs
│   └── app/                                # Fichiers application
├── tests/                                  # Tests PHPUnit
├── public/                                 # Dossier accessible public
├── Dockerfile                              # Configuration Docker
├── docker-compose.yml                      # Orchestration Docker
├── composer.json                           # Dépendances PHP
├── package.json                            # Dépendances Node.js
├── .env.example                            # Modèle variables d'environnement
└── README.md                               # Documentation générale
```

---

## 💾 Modèles de données

### Schéma relationnel

```
┌──────────────────────┐
│       USERS          │
├──────────────────────┤
│ id (PK, UUID)        │
│ phone_number (UQ)    │
│ email (UQ)           │
│ first_name           │
│ last_name            │
│ password (hashed)    │
│ pin_code (hashed)    │
│ cni_number           │
│ kyc_status           │
│ biometrics_active    │
│ otp_code             │
│ otp_expires_at       │
│ is_verified          │
│ status               │
│ last_login_at        │
│ created_at           │
│ updated_at           │
└──────────────────────┘
        │ 1:1
        ├──────────────► WALLETS
        ├──────────────► AUTHENTICATIONS
        ├──────────────► SECURITY_SETTINGS
        ├──────────────► HISTORIES
        └──────────────► CONTACTS

┌──────────────────────┐
│      WALLETS         │
├──────────────────────┤
│ id (PK, UUID)        │
│ user_id (FK)         │
│ balance (decimal)    │
│ currency (XOF)       │
│ account_number       │
│ qr_code              │
│ status               │
│ last_updated         │
│ created_at           │
│ updated_at           │
└──────────────────────┘
        │ 1:M
        └──────────────► TRANSACTIONS

┌──────────────────────┐
│   TRANSACTIONS       │
├──────────────────────┤
│ id (PK, UUID)        │
│ user_id (FK)         │
│ wallet_id (FK)       │
│ type (transfer,dep)  │
│ amount (decimal)     │
│ currency             │
│ status               │
│ description          │
│ metadata (JSON)      │
│ created_at           │
│ updated_at           │
└──────────────────────┘

┌──────────────────────┐
│     TRANSFERS        │
├──────────────────────┤
│ id (PK, UUID)        │
│ transaction_id (FK)  │
│ sender_id (FK)       │
│ receiver_id (FK)     │
│ receiver_phone       │
│ amount               │
│ status (pending...)  │
│ created_at           │
│ updated_at           │
└──────────────────────┘

[Autres tables: AUTHENTICATIONS, HISTORIES, CONTACTS, MERCHANTS, PAYMENTS, etc.]
```

### Exemple: Modèle User

```php
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    // Attributs remplissables
    protected $fillable = [
        'phone_number',    // Identifiant unique
        'first_name',
        'last_name',
        'email',
        'password',        // Hashé automatiquement
        'cni_number',      // Numéro CNI pour KYC
        'kyc_status',      // pending, approved, rejected
        'pin_code',        // Code PIN pour transactions
        'otp_code',        // OTP temporaire
        'otp_expires_at',  // Expiration OTP
        'is_verified',     // Compte vérifié?
    ];

    // Attributs cachés dans les réponses JSON
    protected $hidden = [
        'password',
        'pin_code',
        'otp_code',
    ];

    // Relations
    public function wallet() {
        return $this->hasOne(Wallet::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    // Méthodes JWT requises
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }
}
```

---

## 🌐 API - Routes et endpoints

### Authentification (Publique)

#### 1. Enregistrement
```http
POST /api/register
Content-Type: application/json

{
  "phone_number": "+22145678901",
  "first_name": "Jean",
  "last_name": "Dupont",
  "email": "jean@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123",
  "pin_code": "1234",
  "cni_number": "1234567890ABC"
}

Response (201):
{
  "success": true,
  "message": "Utilisateur créé avec succès",
  "data": {
    "id": "uuid-string",
    "phone_number": "+22145678901",
    "email": "jean@example.com",
    "wallet": { "balance": 0, "currency": "XOF" }
  }
}
```

#### 2. Initier connexion (obtenir OTP)
```http
POST /api/auth/login
Content-Type: application/json

{
  "phone_number": "+22145678901"
}

Response (200):
{
  "success": true,
  "message": "Code OTP envoyé à votre email",
  "data": {
    "user_id": "uuid-string",
    "phone_number": "+22145678901",
    "email": "jean@example.com"
  }
}
```

#### 3. Vérifier OTP et obtenir JWT Token
```http
POST /api/auth/verify-otp
Content-Type: application/json

{
  "phone_number": "+22145678901",
  "otp": "123456"
}

Response (200):
{
  "success": true,
  "message": "OTP vérifié avec succès",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

#### 4. Renvoyer OTP
```http
POST /api/auth/resend-otp
Content-Type: application/json

{
  "phone_number": "+22145678901"
}

Response (200):
{
  "success": true,
  "message": "OTP renvoyé avec succès"
}
```

### Authentification (Protégées - nécessitent JWT Token)

**Header:** `Authorization: Bearer <JWT_TOKEN>`

#### 5. Créer PIN
```http
POST /api/auth/create-pin
Authorization: Bearer <TOKEN>

{
  "pin": "1234"
}

Response (200):
{
  "success": true,
  "message": "Code PIN créé avec succès"
}
```

#### 6. Changer PIN
```http
POST /api/auth/change-pin
Authorization: Bearer <TOKEN>

{
  "old_pin": "1234",
  "new_pin": "5678"
}

Response (200):
{
  "success": true,
  "message": "Code PIN changé avec succès"
}
```

#### 7. Rafraîchir token
```http
POST /api/auth/refresh-token
Authorization: Bearer <TOKEN>

Response (200):
{
  "success": true,
  "message": "Token rafraîchi avec succès",
  "data": {
    "token": "eyJ0eXAi..."
  }
}
```

#### 8. Déconnexion
```http
POST /api/auth/logout
Authorization: Bearer <TOKEN>

Response (200):
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

### Portefeuille (Wallet)

#### 9. Obtenir solde
```http
GET /api/wallet/balance
Authorization: Bearer <TOKEN>

Response (200):
{
  "success": true,
  "data": {
    "balance": 50000,
    "currency": "XOF",
    "status": "active",
    "account_number": "1234567890",
    "last_updated": "2025-11-13T10:30:00Z"
  }
}
```

#### 10. Effectuer dépôt
```http
POST /api/wallet/deposit
Authorization: Bearer <TOKEN>

{
  "amount": 10000,
  "payment_method": "card",
  "description": "Dépôt initial"
}

Response (201):
{
  "success": true,
  "message": "Dépôt effectué avec succès",
  "data": {
    "transaction_id": "uuid-string",
    "amount": 10000,
    "new_balance": 60000,
    "currency": "XOF"
  }
}
```

### Compte (Account Management)

#### 11. Dashboard utilisateur (Tableau de bord)
```http
GET /api/compte/dashboard
Authorization: Bearer <TOKEN>

Response (200):
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid-string",
      "phone_number": "+22145678901",
      "first_name": "Jean",
      "last_name": "Dupont",
      "email": "jean@example.com",
      "kyc_status": "approved",
      "is_verified": true,
      "last_login_at": "2025-11-13T10:30:00Z"
    },
    "compte": {
      "id": "uuid-string",
      "numero_compte": "1234567890",
      "solde": 50000,
      "devise": "XOF",
      "statut": "actif",
      "qr_code": "data:image/png;base64,..."
    },
    "transactions_recentes": [
      {
        "id": "uuid-string",
        "type": "transfer",
        "montant": 5000,
        "direction": "sent",
        "statut": "completed",
        "date": "2025-11-13T10:30:00Z"
      }
    ],
    "statistiques": {
      "total_entrant": 100000,
      "total_sortant": 45000,
      "nombre_transactions": 12,
      "derniere_transaction": "2025-11-13T10:30:00Z"
    }
  }
}
```

#### 12. Obtenir solde du compte par ID
```http
GET /api/comptes/{id}/solde
Authorization: Bearer <TOKEN>

Response (200):
{
  "success": true,
  "data": {
    "compte_id": "uuid-string",
    "numero_compte": "1234567890",
    "solde": 50000,
    "devise": "XOF",
    "statut": "actif",
    "date_mise_a_jour": "2025-11-13T10:30:00Z"
  }
}
```

#### 13. Effectuer dépôt sur le compte
```http
POST /api/compte/{id}/depot
Authorization: Bearer <TOKEN>

{
  "montant": 10000,
  "methode_paiement": "card|bank|mobile",
  "reference_paiement": "TXN123456",
  "description": "Dépôt initial"
}

Response (201):
{
  "success": true,
  "message": "Dépôt effectué avec succès",
  "data": {
    "transaction_id": "uuid-string",
    "compte_id": "uuid-string",
    "montant": 10000,
    "nouveau_solde": 60000,
    "devise": "XOF",
    "methode": "card",
    "statut": "completed",
    "date": "2025-11-13T10:30:00Z"
  }
}
```

#### 14. Effectuer un paiement marchand
```http
POST /api/compte/{id}/payment
Authorization: Bearer <TOKEN>

{
  "montant": 2500,
  "numero_code_marchand": "MERCHANT123",
  "reference_transaction": "PAY123456",
  "description": "Achat produits",
  "pin": "1234"
}

Response (201):
{
  "success": true,
  "message": "Paiement effectué avec succès",
  "data": {
    "transaction_id": "uuid-string",
    "compte_id": "uuid-string",
    "montant": 2500,
    "marchand": "MERCHANT123",
    "nouveau_solde": 57500,
    "statut": "completed",
    "date": "2025-11-13T10:30:00Z",
    "recu": "RECU123456"
  }
}
```

#### 15. Obtenir les transactions du compte
```http
GET /api/compte/{id}/transactions?limit=20&offset=0&type=all
Authorization: Bearer <TOKEN>

Paramètres query:
- limit: nombre de transactions (défaut: 20)
- offset: pagination (défaut: 0)
- type: all|transfer|deposit|payment|withdrawal (défaut: all)
- date_from: YYYY-MM-DD (optionnel)
- date_to: YYYY-MM-DD (optionnel)

Response (200):
{
  "success": true,
  "data": {
    "compte_id": "uuid-string",
    "transactions": [
      {
        "id": "uuid-string",
        "type": "transfer",
        "montant": 5000,
        "direction": "sent|received",
        "partie_liee": {
          "phone": "+22178901234",
          "nom": "Pierre Martin"
        },
        "statut": "completed|pending|failed",
        "reference": "TXN123456",
        "description": "Remboursement",
        "date": "2025-11-13T10:30:00Z"
      },
      {
        "id": "uuid-string",
        "type": "deposit",
        "montant": 10000,
        "direction": "received",
        "methode": "card",
        "statut": "completed",
        "date": "2025-11-12T15:20:00Z"
      },
      {
        "id": "uuid-string",
        "type": "payment",
        "montant": 2500,
        "direction": "sent",
        "marchand": "MERCHANT123",
        "statut": "completed",
        "date": "2025-11-11T12:10:00Z"
      }
    ],
    "pagination": {
      "total": 45,
      "limit": 20,
      "offset": 0,
      "pages": 3
    }
  }
}
```

### Transactions (Transferts)

#### 16. Effectuer transfert
```http
POST /api/transactions/transfer
Authorization: Bearer <TOKEN>

{
  "receiver_phone": "+22178901234",
  "amount": 5000,
  "description": "Remboursement",
  "pin": "1234"
}

Response (201):
{
  "success": true,
  "message": "Transfert effectué avec succès",
  "data": {
    "transaction_id": "uuid-string",
    "transfer_id": "uuid-string",
    "amount": 5000,
    "receiver_phone": "+22178901234",
    "status": "completed",
    "new_balance": 55000,
    "timestamp": "2025-11-13T10:30:00Z"
  }
}
```

#### 17. Obtenir historique transactions
```http
GET /api/transactions/history?limit=20&offset=0
Authorization: Bearer <TOKEN>

Response (200):
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "uuid-string",
        "type": "transfer",
        "amount": 5000,
        "direction": "sent",
        "related_party": "+22178901234",
        "status": "completed",
        "timestamp": "2025-11-13T10:30:00Z",
        "description": "Remboursement"
      }
    ],
    "total": 45,
    "limit": 20,
    "offset": 0
  }
}
```

### Santé API

#### 18. Vérification basique
```http
GET /api/health

Response (200):
{
  "status": "healthy",
  "timestamp": "2025-11-13T10:30:00Z"
}
```

#### 19. Vérification détaillée
```http
GET /api/health/detailed

Response (200):
{
  "status": "healthy",
  "database": "connected",
  "cache": "working",
  "timestamp": "2025-11-13T10:30:00Z"
}
```

### Résumé des endpoints

| Méthode | Route | Authentification | Description |
|---------|-------|-----------------|-------------|
| POST | `/register` | Non | Créer un compte |
| POST | `/auth/login` | Non | Initier connexion (OTP) |
| POST | `/auth/verify-otp` | Non | Vérifier OTP et obtenir JWT |
| POST | `/auth/resend-otp` | Non | Renvoyer OTP |
| POST | `/auth/create-pin` | JWT | Créer code PIN |
| POST | `/auth/change-pin` | JWT | Changer code PIN |
| POST | `/auth/refresh-token` | JWT | Renouveler JWT token |
| POST | `/auth/logout` | JWT | Déconnexion |
| GET | `/wallet/balance` | JWT | Obtenir solde |
| POST | `/wallet/deposit` | JWT | Effectuer dépôt |
| POST | `/transactions/transfer` | JWT | Transfert d'argent |
| GET | `/transactions/history` | JWT | Historique transactions |
| GET | `/health` | Non | Vérification API |
| GET | `/health/detailed` | Non | Vérification détaillée |

---

## 🔄 Processus clés

### 1. Processus d'enregistrement

```
1. Utilisateur soumet données
   └─> phone_number, email, password, PIN, CNI
   
2. Validation
   ├─ Email unique?
   ├─ Phone number unique?
   ├─ Password fort?
   └─ CNI valide?

3. Création compte
   ├─ Hash password (bcrypt)
   ├─ Hash PIN
   ├─ Créer User en BD
   └─ Créer Wallet initial (balance=0)

4. Vérification
   └─> Email de vérification envoyé avec OTP

5. Retour
   └─> UUID utilisateur + info wallet
```

### 2. Processus d'authentification

```
login() → OTP généré et envoyé
   ↓
verifyOtp() → Validation + JWT généré
   ↓
Requête authentifiée → Middleware vérifie token
   ↓
Accès ressource protégée
   ↓
Token expire? → refreshToken() génère nouveau token
   ↓
logout() → Token invalidé
```

### 3. Processus de transfert

```
Utilisateur A → POST /transactions/transfer
   ├─ Données: phone_B, montant, description, PIN_A
   └─ Header: Authorization: Bearer <JWT_A>

Validation (TransactionController):
   ├─ Utilisateur authentifié? (JWT)
   ├─ PIN correct? (comparaison hash)
   ├─ Solde suffisant? (balance >= montant)
   ├─ Utilisateur B existe? (phone_number)
   └─ Montant valide? (> 0, max limite)

Exécution (Transaction):
   ├─ DB Transaction START
   ├─ Débiter account_A: balance -= montant
   ├─ Créditer account_B: balance += montant
   ├─ Créer enregistrement Transfer
   ├─ Créer enregistrements Transaction (2)
   └─ DB Transaction COMMIT

Logging (AuditLogService):
   ├─ Créer audit log
   ├─ Mettre à jour History
   └─ Envoyer notification (email/SMS)

Réponse au client:
   └─> Status: 201 Created
       {
         "success": true,
         "message": "Transfert effectué",
         "data": { transaction_id, amount, status, ... }
       }
```

### 4. Processus de sécurité

#### Hachage des mots de passe
```
Inscription:
  password_raw → bcrypt (12 rounds) → password_hashed (BD)

Connexion:
  password_input → Hash::check(password_input, password_hashed) → true/false
```

#### OTP (One-Time Password)
```
Génération:
  random(100000, 999999) → otp_code
  now() + 15 minutes → otp_expires_at
  Sauvegarder en BD + Envoyer par email

Vérification:
  user.otp_code == request.otp ? → Check expiration → Clear OTP → Return JWT
  Sinon → Erreur
```

#### JWT Token
```
Génération (à la vérification OTP):
  payload = { user_id, iat, exp }
  signature = HMAC_SHA256(header.payload, JWT_SECRET)
  token = header.payload.signature

Validation (sur routes protégées):
  Middleware auth:api:
    ├─ Extraire token du header Authorization
    ├─ Vérifier signature
    ├─ Vérifier expiration
    ├─ Extraire user_id
    └─ Injecter User dans request

Renouvellement:
  token_old → Valider → payload_new = payload_old + exp_new → token_new
```

---

## 🚀 Installation et configuration

### Prérequis

- **PHP:** 8.2 ou supérieur
- **Composer:** Dernière version
- **Node.js:** 16+ (pour frontend)
- **Git:** Pour le versioning
- **Base de données:** SQLite (local) ou MySQL (production)

### Installation locale

#### 1. Cloner le projet
```bash
git clone https://github.com/fatoumatabine/API-OMP.git
cd OMPAY
```

#### 2. Installer dépendances PHP
```bash
composer install
```

#### 3. Copier et configurer .env
```bash
cp .env.example .env
```

Éditer `.env` avec vos valeurs:
```env
APP_NAME=OMPAY
APP_ENV=local
APP_DEBUG=true
APP_KEY=                          # À générer
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite              # ou mysql
DB_DATABASE=database.sqlite

JWT_SECRET=                        # À générer
MAIL_MAILER=log                    # ou configurer SMTP
```

#### 4. Générer clés de chiffrement
```bash
php artisan key:generate
php artisan jwt:secret
```

#### 5. Créer base de données
```bash
touch database/database.sqlite    # Pour SQLite
# ou créer BD MySQL manuellement
```

#### 6. Exécuter migrations
```bash
php artisan migrate
```

#### 7. Installer dépendances frontend
```bash
npm install
npm run build
```

#### 8. Lancer le serveur
```bash
php artisan serve --port=8000
```

ou pour développement complet (avec queue):
```bash
composer run dev
```

### Vérifier l'installation

```bash
# API santé
curl http://localhost:8000/api/health

# Swagger UI
http://localhost:8000/api/docs
```

---

## 📦 Déploiement

### Déploiement avec Docker

#### 1. Build image
```bash
docker build -t ompay:latest .
```

#### 2. Lancer conteneur
```bash
docker-compose up -d
```

#### 3. Exécuter migrations
```bash
docker-compose exec app php artisan migrate --force
```

### Déploiement sur serveur (Linux)

#### 1. Cloner projet
```bash
cd /var/www
git clone https://github.com/fatoumatabine/API-OMP.git ompay
cd ompay
```

#### 2. Installer dépendances
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

#### 3. Configurer environment
```bash
cp .env.production .env
php artisan key:generate
php artisan jwt:secret
```

#### 4. Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap
```

#### 5. Migrations
```bash
php artisan migrate --force
```

#### 6. Configurer serveur web (Nginx)
```nginx
server {
    listen 80;
    server_name api.ompay.com;
    
    root /var/www/ompay/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 7. Configurer PHP-FPM et démarrer
```bash
sudo systemctl restart php8.2-fpm nginx
```

---

## ⌨️ Commandes complètes intégrées

### Installation et démarrage local (Complète)

```bash
# 1. Cloner le projet
git clone https://github.com/fatoumatabine/API-OMP.git
cd OMPAY

# 2. Installer dépendances PHP et Node
composer install
npm install

# 3. Configurer l'environnement
cp .env.example .env

# 4. Générer clés de chiffrement
php artisan key:generate
php artisan jwt:secret

# 5. Créer base de données SQLite
touch database/database.sqlite

# 6. Exécuter les migrations
php artisan migrate

# 7. Compiler les assets frontend
npm run build

# 8. Démarrer le serveur
php artisan serve --port=8000

# ✅ Vérifier: http://localhost:8000/api/health
```

### Setup rapide (une seule commande)

```bash
composer run-script setup
```

### Démarrage développement avec auto-reload

```bash
# Terminal 1: Serveur Laravel
php artisan serve --port=8000

# Terminal 2: Queue (jobs en arrière-plan)
php artisan queue:listen --tries=1

# Terminal 3: Logs en temps réel
php artisan pail --timeout=0

# Terminal 4: Compilation frontend
npm run dev
```

Ou tout en une commande:
```bash
composer run dev
```

### Base de données - Migrations

```bash
# Créer une nouvelle migration
php artisan make:migration create_table_name_table

# Exécuter toutes les migrations
php artisan migrate

# Exécuter migrations spécifiques
php artisan migrate --path=database/migrations/2025_11_13_create_users_table.php

# Annuler la dernière migration
php artisan migrate:rollback

# Annuler toutes les migrations
php artisan migrate:reset

# Réinitialiser BD complètement
php artisan migrate:refresh

# Réinitialiser + seeder
php artisan migrate:refresh --seed

# Afficher les migrations exécutées
php artisan migrate:status

# Forcer les migrations (production)
php artisan migrate --force

# Rollback spécifique
php artisan migrate:rollback --step=1
```

### Base de données - Seeders

```bash
# Créer un seeder
php artisan make:seeder UserSeeder

# Exécuter les seeders
php artisan db:seed

# Exécuter un seeder spécifique
php artisan db:seed --class=UserSeeder

# Réinitialiser et seeder
php artisan migrate:refresh --seed
```

### Génération de code

```bash
# Models
php artisan make:model YourModel -m  # Avec migration
php artisan make:model YourModel -c  # Avec controller
php artisan make:model YourModel -cr # Avec controller et migration
php artisan make:model YourModel -a  # Tout (migration, controller, factory, seeder)

# Controllers
php artisan make:controller YourController -r  # Ressource (CRUD)
php artisan make:controller YourController -m YourModel  # Lié au model

# Requests (validation)
php artisan make:request YourRequest

# Services
php artisan make:service YourService

# Middleware
php artisan make:middleware YourMiddleware

# Jobs
php artisan make:job YourJob

# Events
php artisan make:event YourEvent

# Listeners
php artisan make:listener YourListener

# Mails
php artisan make:mail YourMail

# Tests
php artisan make:test YourTest
php artisan make:test YourTest --unit
```

### Testing

```bash
# Exécuter tous les tests
php artisan test

# Tester un fichier spécifique
php artisan test tests/Feature/YourTest.php

# Tester une méthode spécifique
php artisan test --filter test_example

# Tests avec coverage (couverture code)
php artisan test --coverage

# Tests avec verbosité
php artisan test -v

# Tests en parallèle (plus rapide)
php artisan test --parallel

# Vider le cache de test
rm -rf .phpunit.result.cache
```

### Cache et clearing

```bash
# Vider tout le cache
php artisan cache:clear

# Vider le cache de config
php artisan config:clear

# Vider le cache de routes
php artisan route:clear

# Vider le cache de vues
php artisan view:clear

# Vider le cache d'optimisation
php artisan optimize:clear

# Tout nettoyer en une commande
php artisan optimize:clear && php artisan cache:clear && php artisan config:clear
```

### Artisan Tinker (Terminal interactif)

```bash
# Lancer Tinker
php artisan tinker

# Dans Tinker:
>>> $user = User::find('uuid');
>>> $user->wallet;
>>> $user->transactions()->count();
>>> User::where('phone_number', '+22145678901')->first();
>>> exit;
```

### Logs et debugging

```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Logs avec grep (chercher erreurs)
grep -i "error" storage/logs/laravel.log

# Logs en temps réel avec couleurs
php artisan pail

# Logs filtré par level
php artisan pail --level=error

# Vider les logs
rm storage/logs/laravel.log
```

### JWT Authentication

```bash
# Générer JWT secret
php artisan jwt:secret

# Invalider les tokens (utile après secret change)
# Pas de commande directe, dépend de l'implémentation

# Token expirera après 1 heure par défaut (voir config/jwt.php)
```

### Swagger/API Docs

```bash
# Générer la documentation Swagger
php artisan l5-swagger:generate

# Supprimer la documentation
php artisan l5-swagger:clean

# Regenerate docs
php artisan l5-swagger:generate --clean

# Voir les docs: http://localhost:8000/api/docs
```

### Database inspection

```bash
# Voir la structure d'une table
php artisan tinker
>>> Schema::getColumns('users');
>>> Schema::getIndexes('users');
>>> exit;

# Ou avec MySQL CLI
mysql -u root -p ompay
> DESCRIBE users;
> SHOW TABLES;
> EXIT;
```

### File Management

```bash
# Lier storage public
php artisan storage:link

# Vérifier les symlinks
ls -la public/storage

# Nettoyer les fichiers temporaires
php artisan storage:clean
```

### Queue (Jobs en arrière-plan)

```bash
# Écouter la queue
php artisan queue:listen

# Écouter avec timeout
php artisan queue:listen --timeout=60 --tries=1

# Lister les jobs en attente
php artisan queue:failed

# Retenter les jobs échoués
php artisan queue:retry all

# Nettoyer les jobs échoués
php artisan queue:flush

# Travailler une seule fois
php artisan queue:work --once
```

### Permissions et ownership

```bash
# Définir permissions storage
chmod -R 755 storage bootstrap/cache

# Définir propriétaire (Ubuntu/Linux)
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chown -R $(whoami) storage bootstrap/cache

# Permissions spécifiques
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Docker - Complète

```bash
# Construire l'image
docker build -t ompay:latest .

# Lancer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f app

# Exécuter une commande dans le conteneur
docker-compose exec app php artisan migrate

# Voir les conteneurs en cours
docker-compose ps

# Nettoyer les volumes (ATTENTION!)
docker-compose down -v

# Rebuild complet
docker-compose down -v && docker-compose up -d

# Accéder au shell du conteneur
docker-compose exec app sh

# Voir les images
docker images

# Supprimer une image
docker rmi ompay:latest
```

### Production Deployment - Checklist

```bash
# 1. Clone et setup
git clone https://github.com/fatoumatabine/API-OMP.git /var/www/ompay
cd /var/www/ompay

# 2. Install dependencies (sans dev)
composer install --no-dev --optimize-autoloader

# 3. Install frontend
npm ci && npm run build

# 4. Configure environment
cp .env.example .env.production
# Éditer .env.production avec values production
cat > .env << EOF
APP_NAME=OMPAY
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=https://api.ompay.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ompay_prod
DB_USERNAME=ompay_user
DB_PASSWORD=SecurePassword123
JWT_SECRET=
MAIL_MAILER=smtp
EOF

# 5. Generate keys
php artisan key:generate --force
php artisan jwt:secret --force

# 6. Database setup
php artisan migrate --force
php artisan db:seed --force

# 7. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Permissions
sudo chown -R www-data:www-data /var/www/ompay
chmod -R 755 /var/www/ompay/storage /var/www/ompay/bootstrap/cache

# 9. WebServer restart
sudo systemctl restart nginx php8.2-fpm

# 10. Verify
curl https://api.ompay.com/api/health
```

### Git Commands

```bash
# Configuration initiale
git config --global user.name "Your Name"
git config --global user.email "your@email.com"

# Clone
git clone https://github.com/fatoumatabine/API-OMP.git

# Voir le statut
git status

# Ajouter les changements
git add .
git add app/Models/User.php  # Fichier spécifique

# Commit
git commit -m "feat: add new feature"
git commit -m "fix: resolve issue"
git commit -m "docs: update documentation"

# Voir les logs
git log --oneline
git log --graph --all --decorate

# Branches
git branch                    # Voir branches locales
git branch -a                 # Voir toutes les branches
git branch feature/new        # Créer une branche
git checkout feature/new      # Switch vers branche
git checkout -b feature/new   # Créer et switch

# Push et Pull
git push origin main
git push origin feature/new
git pull origin main

# Merge
git merge feature/new
git rebase main

# Diff
git diff
git diff app/Models/User.php

# Stash (sauvegarder temporaire)
git stash
git stash pop

# Revert un commit
git revert <commit-sha>

# Reset (ATTENTION!)
git reset --soft HEAD~1
git reset --hard HEAD~1
```

### Postman Testing

```bash
# Importer la collection
# Postman → File → Import → OMPAY.postman_collection.json

# Endpoints automatiques:
POST /api/register              # Créer compte
POST /api/auth/login            # Initier login (OTP)
POST /api/auth/verify-otp       # Vérifier OTP
GET  /api/wallet/balance        # Solde
POST /api/transactions/transfer # Transfert
GET  /api/transactions/history  # Historique
POST /api/auth/logout           # Logout

# Variables Postman à configurer:
{{base_url}} = http://localhost:8000
{{token}} = JWT token obtenu
{{phone_number}} = +22145678901
```

### Curl - Test API

```bash
# Health check
curl http://localhost:8000/api/health

# Enregistrement
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145678901",
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean@example.com",
    "password": "Password123",
    "password_confirmation": "Password123",
    "pin_code": "1234",
    "cni_number": "1234567890ABC"
  }'

# Login (obtenir OTP)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone_number": "+22145678901"}'

# Vérifier OTP (obtenir JWT)
curl -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone_number": "+22145678901", "otp": "123456"}'

# Requête authentifiée
curl -X GET http://localhost:8000/api/wallet/balance \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Transfert
curl -X POST http://localhost:8000/api/transactions/transfer \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver_phone": "+22178901234",
    "amount": 5000,
    "pin": "1234",
    "description": "Payment"
  }'
```

### Nginx Configuration

```bash
# Créer le fichier de config
sudo nano /etc/nginx/sites-available/ompay.conf

# Contenu:
server {
    listen 80;
    listen [::]:80;
    server_name api.ompay.com;
    
    root /var/www/ompay/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Activer la config
sudo ln -s /etc/nginx/sites-available/ompay.conf /etc/nginx/sites-enabled/

# Tester la config
sudo nginx -t

# Restart
sudo systemctl restart nginx
```

### Systemd Service (Auto-start)

```bash
# Créer le service
sudo nano /etc/systemd/system/ompay-queue.service

# Contenu:
[Unit]
Description=OMPAY Queue Worker
After=network.target

[Service]
User=www-data
WorkingDirectory=/var/www/ompay
ExecStart=/usr/bin/php artisan queue:listen
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target

# Activer et démarrer
sudo systemctl daemon-reload
sudo systemctl enable ompay-queue.service
sudo systemctl start ompay-queue.service

# Vérifier le statut
sudo systemctl status ompay-queue.service

# Logs
sudo journalctl -u ompay-queue.service -f
```

### Sécurité et hardening

```bash
# Générer une clé SSH pour GitHub
ssh-keygen -t ed25519 -C "your@email.com"
cat ~/.ssh/id_ed25519.pub  # Copier dans GitHub settings

# Configurer HTTPS (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d api.ompay.com
sudo certbot renew --dry-run  # Tester l'auto-renewal

# Firewall
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw enable

# Vérifier les variables sensibles
grep -r "password\|secret\|key" .env*

# Audit des dépendances
composer audit
npm audit

# Update dépendances
composer update --no-dev
npm update
```

### Analytics et monitoring

```bash
# Voir l'utilisation disque
df -h
du -sh /var/www/ompay

# Voir l'utilisation CPU/RAM
top
htop

# Logs d'erreurs
tail -100 storage/logs/laravel.log | grep -i "error"

# Nombre de requêtes
wc -l storage/logs/laravel.log

# Requêtes lentes (> 1 sec)
grep "Processed in" storage/logs/laravel.log | awk '{print $NF}' | sort -n | tail
```

### Cleanup et maintenance

```bash
# Nettoyer les fichiers temporaires
php artisan optimize:clear

# Nettoyer les logs anciens
find storage/logs -type f -mtime +30 -delete

# Nettoyer les fichiers uploadés orphelins
php artisan storage:clean

# Optimiser la BD (MySQL)
php artisan tinker
>>> DB::statement('OPTIMIZE TABLE users');
>>> exit;

# Vérifier l'intégrité
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit;

# Backup BD (MySQL)
mysqldump -u root -p ompay > backup-$(date +%Y%m%d).sql

# Restore BD
mysql -u root -p ompay < backup-20251113.sql
```

---

## 📚 Interface Swagger - Documentation API Interactive

### Accès à Swagger UI

#### URL directe
```
http://localhost:8000/api/docs
http://localhost:8000/api/documentation
```

#### Fichier source OpenAPI
```
storage/api-docs/swagger.yaml
```

### Vue d'ensemble Swagger UI

L'interface Swagger présente:
- **Version API:** 1.0.0
- **Titre:** OMPAY API
- **Description:** Plateforme de paiement mobile et portefeuille numérique
- **Licence:** MIT
- **Contact Support:** support@ompay.com

### Structure des endpoints Swagger

#### Authentification (Authentication)

**Endpoints disponibles:**

1. **POST /api/auth/login**
   - Description: Connectez-vous avec le code OTP
   - Méthode: POST
   - Auth requise: Non
   - Paramètres:
     ```json
     {
       "phone_number": "+22145678901"
     }
     ```
   - Réponse: 200, 404, 422

2. **POST /api/auth/verify-otp**
   - Description: Vérifiez le code OTP et obtenez un jeton JWT
   - Méthode: POST
   - Auth requise: Non
   - Paramètres:
     ```json
     {
       "phone_number": "+22145678901",
       "otp": "123456"
     }
     ```
   - Réponse: 200 (avec token), 400, 404

3. **POST /api/auth/resend-otp**
   - Description: Renvoyer le code OTP
   - Méthode: POST
   - Auth requise: Non
   - Paramètres:
     ```json
     {
       "phone_number": "+22145678901"
     }
     ```
   - Réponse: 200, 400, 404

4. **POST /api/register**
   - Description: Inscription d'un nouvel utilisateur
   - Méthode: POST
   - Auth requise: Non
   - Paramètres:
     ```json
     {
       "phone_number": "+22145678901",
       "first_name": "Jean",
       "last_name": "Dupont",
       "email": "jean@example.com",
       "password": "Password123",
       "password_confirmation": "Password123",
       "pin_code": "1234",
       "cni_number": "1234567890ABC"
     }
     ```
   - Réponse: 201, 422

5. **POST /api/auth/create-pin**
   - Description: Créer un code PIN de transaction
   - Méthode: POST
   - Auth requise: Oui (JWT)
   - Paramètres:
     ```json
     {
       "pin": "1234"
     }
     ```
   - Réponse: 200, 400, 401

6. **POST /api/auth/change-pin**
   - Description: Modifier le code PIN de la transaction
   - Méthode: POST
   - Auth requise: Oui (JWT)
   - Paramètres:
     ```json
     {
       "old_pin": "1234",
       "new_pin": "5678"
     }
     ```
   - Réponse: 200, 401

7. **POST /api/auth/refresh-token**
   - Description: Actualiser le jeton d'accès
   - Méthode: POST
   - Auth requise: Oui (JWT)
   - Réponse: 200, 401

8. **POST /api/auth/logout**
   - Description: Déconnexion
   - Méthode: POST
   - Auth requise: Oui (JWT)
   - Réponse: 200, 401

#### Portefeuille (Wallet)

**Endpoints disponibles:**

1. **GET /api/wallet/balance**
   - Description: Consulter le solde de votre portefeuille
   - Méthode: GET
   - Auth requise: Oui (JWT)
   - Réponse: 200 avec balance, devise, statut

2. **POST /api/wallet/deposit**
   - Description: Déposer de l'argent
   - Méthode: POST
   - Auth requise: Oui (JWT)
   - Paramètres:
     ```json
     {
       "amount": 10000,
       "payment_method": "card",
       "description": "Dépôt initial"
     }
     ```
   - Réponse: 201, 400, 422

#### Transactions

**Endpoints disponibles:**

1. **POST /api/transactions/transfer**
   - Description: Transférer de l'argent
   - Méthode: POST
   - Auth requise: Oui (JWT)
   - Paramètres:
     ```json
     {
       "receiver_phone": "+22178901234",
       "amount": 5000,
       "description": "Remboursement",
       "pin": "1234"
     }
     ```
   - Réponse: 201, 400, 401, 422

2. **GET /api/transactions/history**
   - Description: Consulter l'historique des transactions
   - Méthode: GET
   - Auth requise: Oui (JWT)
   - Paramètres query:
     - `limit`: 20 (par défaut)
     - `offset`: 0 (par défaut)
   - Réponse: 200 avec liste transactions

### Schémas Swagger

#### Utilisateur (User)
```json
{
  "id": "uuid-string",
  "phone_number": "+22145678901",
  "email": "jean@example.com",
  "first_name": "Jean",
  "last_name": "Dupont",
  "kyc_status": "pending|approved|rejected",
  "is_verified": true|false,
  "created_at": "2025-11-13T10:30:00Z",
  "updated_at": "2025-11-13T10:30:00Z"
}
```

#### Portefeuille (Wallet)
```json
{
  "id": "uuid-string",
  "user_id": "uuid-string",
  "balance": 50000,
  "currency": "XOF",
  "account_number": "1234567890",
  "qr_code": "data:image/png;base64,...",
  "status": "active|inactive",
  "last_updated": "2025-11-13T10:30:00Z"
}
```

#### Transaction
```json
{
  "id": "uuid-string",
  "user_id": "uuid-string",
  "wallet_id": "uuid-string",
  "type": "transfer|deposit|withdrawal|payment",
  "amount": 5000,
  "currency": "XOF",
  "status": "pending|completed|failed",
  "description": "Remboursement",
  "metadata": {},
  "created_at": "2025-11-13T10:30:00Z"
}
```

#### Transfert (Transfer)
```json
{
  "id": "uuid-string",
  "transaction_id": "uuid-string",
  "sender_id": "uuid-string",
  "receiver_id": "uuid-string",
  "receiver_phone": "+22178901234",
  "amount": 5000,
  "status": "pending|completed|failed",
  "created_at": "2025-11-13T10:30:00Z"
}
```

#### Erreur de validation
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "phone_number": ["Le numéro de téléphone est requis"],
    "email": ["L'adresse email doit être unique"]
  }
}
```

#### Erreur générale
```json
{
  "success": false,
  "message": "Une erreur s'est produite",
  "data": null
}
```

### Codes HTTP Swagger

| Code | Description |
|------|-------------|
| 200 | OK - Requête réussie |
| 201 | Created - Ressource créée |
| 400 | Bad Request - Requête invalide |
| 401 | Unauthorized - Non authentifié |
| 404 | Not Found - Ressource non trouvée |
| 422 | Unprocessable Entity - Erreur de validation |
| 500 | Internal Server Error - Erreur serveur |

### Sécurité dans Swagger

#### BearerAuth (JWT)
```
Type: HTTP
Scheme: Bearer
Format: JWT
Description: Jeton JWT obtenu après vérification OTP
Exemple: Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

#### Utilisation
1. Obtenir un token via `/api/auth/verify-otp`
2. Cliquer sur le bouton "Authorize" en haut à droite
3. Entrer le token: `Bearer <votre_token>`
4. Cliquer sur "Authorize"
5. Les endpoints protégés incluront automatiquement le header

### Générer et mettre à jour Swagger

#### Générer la documentation
```bash
php artisan l5-swagger:generate
```

#### Nettoyer et régénérer
```bash
php artisan l5-swagger:generate --clean
```

#### Supprimer la documentation
```bash
php artisan l5-swagger:clean
```

### Ajouter des annotations Swagger dans le code

#### Controller avec annotations OpenAPI
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints pour l'authentification"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Connectez-vous avec le code OTP",
     *     description="Initiez une connexion avec votre numéro de téléphone pour recevoir un OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Numéro de téléphone",
     *         @OA\JsonContent(
     *             required={"phone_number"},
     *             @OA\Property(
     *                 property="phone_number",
     *                 type="string",
     *                 example="+22145678901",
     *                 description="Numéro de téléphone au format international"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string"),
     *                 @OA\Property(property="phone_number", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     */
    public function login(): JsonResponse
    {
        // Logique du contrôleur
    }
}
```

#### Model avec annotations
```php
<?php

/**
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="phone_number", type="string", example="+22145678901"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="first_name", type="string", example="Jean"),
 *     @OA\Property(property="last_name", type="string", example="Dupont"),
 *     @OA\Property(property="kyc_status", type="string", enum={"pending", "approved", "rejected"}),
 *     @OA\Property(property="is_verified", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class User extends Model
{
    // Code du modèle
}
```

### Configuration Swagger (config/l5-swagger.php)

```php
return [
    'api' => [
        'title' => 'OMPAY API',
        'description' => 'Plateforme de paiement mobile',
        'version' => '1.0.0',
    ],

    'routes' => [
        'api' => 'api/documentation',
        'docs' => 'api/docs',
        'oauth2_callback' => 'api/oauth2-callback',
    ],

    'paths' => [
        'docs_json' => 'api-docs.json',
        'docs_yaml' => 'swagger.yaml',
        'base' => base_path('storage/api-docs'),
    ],

    'servers' => [
        [
            'url' => env('APP_URL', 'http://127.0.0.1:8000'),
            'description' => 'Serveur de développement',
        ],
    ],

    'security' => [
        'api_key_security_scheme' => [
            'type' => 'apiKey',
            'description' => 'API Key',
            'name' => 'X-API-KEY',
            'in' => 'header',
        ],
    ],
];
```

### Télécharger la documentation

#### Format JSON
```
http://localhost:8000/api-docs.json
```

#### Format YAML
```
http://localhost:8000/api/documentation
```

### Importer dans Postman depuis Swagger

1. Copier l'URL: `http://localhost:8000/api-docs.json`
2. Postman → Import → Link → Paste URL
3. Ou télécharger et importer manuellement

### Tests interactifs dans Swagger UI

1. Cliquer sur un endpoint (ex: POST /api/auth/login)
2. Cliquer "Try it out"
3. Remplir les paramètres
4. Cliquer "Execute"
5. Voir la réponse en temps réel

### Personnaliser Swagger UI

#### Ajouter un logo
```php
// dans config/l5-swagger.php
'swagger_ui_settings' => [
    'persistAuthorization' => true,
    'displayOperationId' => false,
    'deepLinking' => true,
    'presets' => [
        'swagger_ui_standalone_preset',
        'swagger_ui_settings',
    ],
],
```

#### Thème personnalisé
Éditer `resources/views/vendor/l5-swagger/index.blade.php`

### Documentation complète Swagger (swagger.yaml)

```yaml
openapi: 3.0.0
info:
  title: OMPAY API
  version: 1.0.0
  description: Plateforme de paiement mobile et portefeuille numérique
  contact:
    name: Support API OMPAY
    email: support@ompay.com
  license:
    name: MIT

servers:
  - url: http://127.0.0.1:8000
    description: Serveur de développement

security:
  - bearerAuth: []

tags:
  - name: Authentication
    description: Endpoints pour l'authentification
  - name: Wallet
    description: Endpoints de gestion de portefeuille
  - name: Transactions
    description: Endpoints de transaction

paths:
  /api/register:
    post:
      summary: Créer un compte utilisateur
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                phone_number:
                  type: string
                  example: "+22145678901"
                first_name:
                  type: string
                  example: "Jean"
                last_name:
                  type: string
                  example: "Dupont"
                email:
                  type: string
                  format: email
                  example: "jean@example.com"
                password:
                  type: string
                  example: "Password123"
                password_confirmation:
                  type: string
                  example: "Password123"
                pin_code:
                  type: string
                  example: "1234"
                cni_number:
                  type: string
                  example: "1234567890ABC"
      responses:
        '201':
          description: Utilisateur créé avec succès
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                    example: true
                  message:
                    type: string
                  data:
                    $ref: '#/components/schemas/User'
        '422':
          description: Erreur de validation
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'

  /api/wallet/balance:
    get:
      summary: Obtenir le solde du portefeuille
      tags:
        - Wallet
      security:
        - bearerAuth: []
      responses:
        '200':
          description: Solde obtenu
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    $ref: '#/components/schemas/Wallet'
        '401':
          description: Non authentifié

  /api/transactions/transfer:
    post:
      summary: Effectuer un transfert
      tags:
        - Transactions
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                receiver_phone:
                  type: string
                  example: "+22178901234"
                amount:
                  type: number
                  example: 5000
                description:
                  type: string
                  example: "Remboursement"
                pin:
                  type: string
                  example: "1234"
      responses:
        '201':
          description: Transfert effectué
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    $ref: '#/components/schemas/Transfer'

components:
  schemas:
    User:
      type: object
      properties:
        id:
          type: string
          format: uuid
        phone_number:
          type: string
        email:
          type: string
        first_name:
          type: string
        last_name:
          type: string
        kyc_status:
          type: string
          enum: [pending, approved, rejected]
        is_verified:
          type: boolean
        created_at:
          type: string
          format: date-time

    Wallet:
      type: object
      properties:
        id:
          type: string
          format: uuid
        balance:
          type: number
        currency:
          type: string
          example: "XOF"
        status:
          type: string
          enum: [active, inactive]

    Transfer:
      type: object
      properties:
        id:
          type: string
          format: uuid
        amount:
          type: number
        status:
          type: string
        created_at:
          type: string
          format: date-time

    ValidationError:
      type: object
      properties:
        success:
          type: boolean
          example: false
        errors:
          type: object

  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
      description: "Jeton JWT obtenu après vérification OTP"
```

### Troubleshooting Swagger

#### Swagger ne se met pas à jour
```bash
# Nettoyer le cache
php artisan cache:clear

# Régénérer
php artisan l5-swagger:generate --clean
```

#### "Token not provided" en testant
1. Obtenir d'abord un token via `/api/auth/verify-otp`
2. Cliquer "Authorize"
3. Entrer: `Bearer <token>`
4. Puis tester les endpoints protégés

#### Endpoints manquants
- Vérifier que les annotations `@OA\` sont correctes
- Vérifier le chemin du fichier de controller
- Régénérer avec: `php artisan l5-swagger:generate --clean`

---

## 👨‍💻 Guide de développement

### Structure d'un Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\YourRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\YourModel;
use Illuminate\Http\JsonResponse;

class YourController extends Controller
{
    use ApiResponseTrait;  // Pour réponses uniformes

    // Exemple: endpoint GET
    public function index(): JsonResponse
    {
        try {
            $data = YourModel::all();
            return $this->successResponse($data, 'Data retrieved');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Exemple: endpoint POST
    public function store(YourRequest $request): JsonResponse
    {
        try {
            $data = YourModel::create($request->validated());
            return $this->successResponse($data, 'Created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
```

### Structure d'un Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class YourModel extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['field1', 'field2'];

    protected $casts = [
        'date_field' => 'datetime',
    ];

    // Relations
    public function relatedModel()
    {
        return $this->hasMany(RelatedModel::class);
    }
}
```

### Créer une migration

```bash
php artisan make:migration create_your_table_table
```

Migration file:
```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_table', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignUuid('user_id')->constrained();
            $table->timestamps();
            
            $table->index('user_id');
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('your_table');
    }
};
```

Exécuter:
```bash
php artisan migrate
```

### Créer une Form Request

```bash
php artisan make:request YourRequest
```

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class YourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field1' => 'required|string|max:255',
            'field2' => 'required|email|unique:users,email',
            'amount' => 'required|numeric|min:1|max:1000000',
        ];
    }

    public function messages(): array
    {
        return [
            'field1.required' => 'Le champ 1 est requis',
        ];
    }
}
```

### Utiliser les Services

```bash
php artisan make:service YourService
```

```php
<?php

namespace App\Services;

class YourService
{
    public function doSomething($param)
    {
        // Logique métier
        return $result;
    }
}
```

Utiliser dans un Controller:
```php
private YourService $service;

public function __construct(YourService $service)
{
    $this->service = $service;
}

public function action()
{
    $result = $this->service->doSomething($param);
    return $this->successResponse($result);
}
```

### Ajouter un endpoint API

1. **Créer la Form Request** (validation)
2. **Créer la méthode dans le Controller**
3. **Ajouter la route** dans `routes/api.php`
4. **Tester** avec Postman/curl

Exemple: Ajouter un endpoint pour retirer de l'argent

```php
// 1. Form Request: app/Http/Requests/WithdrawRequest.php
class WithdrawRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'pin' => 'required|string|size:4',
            'bank_account' => 'required|string',
        ];
    }
}

// 2. Controller: app/Http/Controllers/WalletController.php
public function withdraw(WithdrawRequest $request): JsonResponse
{
    $user = Auth::user();
    $wallet = $user->wallet;

    // Validation
    if (!Hash::check($request->pin, $user->pin_code)) {
        return $this->errorResponse('PIN invalide', 401);
    }

    if ($wallet->balance < $request->amount) {
        return $this->errorResponse('Solde insuffisant', 400);
    }

    // Exécution
    DB::transaction(function () use ($wallet, $user, $request) {
        $wallet->decrement('balance', $request->amount);
        
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'status' => 'completed',
        ]);
    });

    return $this->successResponse(
        ['new_balance' => $wallet->balance],
        'Retrait effectué',
        201
    );
}

// 3. Route: routes/api.php
Route::post('wallet/withdraw', [WalletController::class, 'withdraw'])
    ->middleware('auth:api');
```

### Testing

Exécuter les tests:
```bash
php artisan test
```

Créer un test:
```bash
php artisan make:test YourTest
```

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class YourTest extends TestCase
{
    public function test_example(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/your-endpoint', [...]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }
}
```

### Bonnes pratiques

✅ **À faire:**
- Utiliser des migrations pour tous les changements de schéma
- Valider avec Form Requests
- Utiliser les Services pour la logique métier
- Encapsuler les transactions sensibles dans DB::transaction()
- Logger les actions importantes
- Documenter les endpoints avec OpenAPI/Swagger
- Utiliser des UUIDs pour les IDs primaires
- Hacher les mots de passe et PINs
- Implémenter le rate limiting

❌ **À éviter:**
- Requêtes SQL brutes (utiliser Eloquent)
- Logique métier dans les Controllers
- Stocker des mots de passe en clair
- Exposer des informations sensibles (password, PIN)
- Transactions sans atomicité
- Endpoints sans authentification (si nécessaire)

---

## 📚 Ressources et documentation

### Fichiers de documentation du projet
- `API_DOCUMENTATION.md` - Documentation API complète
- `ROUTES.md` - Détail des routes
- `QUICK_START.md` - Démarrage rapide
- `DEPLOYMENT_CHECKLIST.md` - Liste vérification déploiement

### Documentation externe
- [Laravel Documentation](https://laravel.com/docs)
- [JWT Auth Package](https://github.com/tymondesigns/jwt-auth)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [API Documentation (Swagger)](http://localhost:8000/api/docs)

### Collection Postman
Importer `OMPAY.postman_collection.json` dans Postman pour tester les endpoints

---

## 🆘 Troubleshooting

### Erreur: "SQLSTATE[HY000]: General error: 1 database disk image is malformed"
```bash
# Supprimer et recréer la BD SQLite
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate
```

### Erreur: "Token not provided"
- Vérifier l'header Authorization
- Format correct: `Authorization: Bearer <token>`

### Erreur: "Call to undefined method"
```bash
composer dump-autoload
```

### Port 8000 déjà utilisé
```bash
php artisan serve --port=8001
```

### Permissions fichiers
```bash
chmod -R 755 storage bootstrap/cache
chown -R $(whoami) storage bootstrap/cache
```

---

## 📝 Checklist pour reproduire le projet

- [ ] Cloner le repository
- [ ] Installer composer: `composer install`
- [ ] Copier .env: `cp .env.example .env`
- [ ] Générer clés: `php artisan key:generate && php artisan jwt:secret`
- [ ] Créer BD: `touch database/database.sqlite`
- [ ] Migrations: `php artisan migrate`
- [ ] Installer npm: `npm install && npm run build`
- [ ] Lancer serveur: `php artisan serve`
- [ ] Vérifier santé: `GET http://localhost:8000/api/health`
- [ ] Consulter docs: `http://localhost:8000/api/docs`
- [ ] Importer Postman: `OMPAY.postman_collection.json`
- [ ] Tester un endpoint: `POST /api/register`

---

## 📞 Support et contact

Pour toute question ou problème:
- GitHub Issues: [Lien repo](https://github.com/fatoumatabine/API-OMP/issues)
- Documentation: Voir fichiers `.md` du projet
- API Docs: Swagger UI à `/api/docs`

---

**Document créé:** Novembre 2025  
**Version:** 1.0.0  
**Statut:** Complet et prêt pour production  
**Maintenance:** À jour avec le code source

# Routes API OMPAY

## 📍 Base URL
```
http://127.0.0.1:8001/api
```

## 🔐 Routes publiques (sans authentification)

### Authentification

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/register` | Créer un nouveau compte utilisateur |
| POST | `/auth/login` | Se connecter et obtenir un JWT token |
| POST | `/auth/verify-otp` | Vérifier un code OTP |
| POST | `/auth/resend-otp` | Renvoyer un code OTP |

### Documentation

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/documentation` | Récupérer le fichier swagger.yaml |
| GET | `/docs` | Afficher la documentation Swagger en HTML |

---

## 🔒 Routes protégées (nécessitent un JWT token)

Toutes les routes protégées nécessitent l'en-tête :
```
Authorization: Bearer YOUR_JWT_TOKEN
```

### Authentification

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/auth/create-pin` | Créer un code PIN (si pas déjà créé) |
| POST | `/auth/change-pin` | Changer le code PIN |
| POST | `/auth/refresh-token` | Rafraîchir le JWT token |
| POST | `/auth/logout` | Se déconnecter (invalide le token) |

### Portefeuille (Wallet)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/wallet/balance` | Obtenir le solde du portefeuille |
| POST | `/wallet/deposit` | Effectuer un dépôt d'argent |

### Transactions

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/transactions/transfer` | Effectuer un transfert d'argent |
| GET | `/transactions/history` | Obtenir l'historique des transactions |

---

## 📊 Statistiques

- **Routes totales:** 14
- **Routes publiques:** 6
- **Routes protégées:** 8
- **Contrôleurs:** 4 (Auth, Compte, Wallet, Transaction)

---

## 🎯 Exemples d'utilisation

### 1. Créer un compte
```bash
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
```

### 2. Se connecter
```bash
curl -X POST http://127.0.0.1:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+22145678901",
    "password": "password123"
  }'
```

### 3. Obtenir le solde
```bash
curl -X GET http://127.0.0.1:8001/api/wallet/balance \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Effectuer un transfert
```bash
curl -X POST http://127.0.0.1:8001/api/transactions/transfer \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "receiver_phone": "+22178901234",
    "amount": 5000,
    "description": "Remboursement",
    "pin": "1234"
  }'
```

---

## 🛠️ Accéder à la documentation

### Option 1: Swagger HTML (Recommandé)
```
http://127.0.0.1:8001/api/docs
```

### Option 2: Fichier OpenAPI YAML
```
http://127.0.0.1:8001/api/documentation
```

### Option 3: Importer dans Postman
1. Ouvrir Postman
2. Collections → Import
3. Choisir `OMPAY.postman_collection.json`
4. Définir la variable `base_url` à `http://127.0.0.1:8001`

---

## 🚀 Scripts de test

### Tester l'inscription
```bash
bash test_register.sh
```

### Tester tous les endpoints
```bash
bash test_all_api.sh
```

---

## 📝 Middleware

Les routes protégées utilisent les middleware suivants :
- `auth:api` - Authentification JWT
- `log.creation` - Logging des créations

---

## ⚙️ Configuration

### Fichier de configuration
```
routes/api.php
```

### Authentification
- **Driver:** JWT (Tymon\JWTAuth)
- **Guard:** api

### Rate Limiting
À configurer dans `app/Http/Middleware/`

---

## 🔄 Flux d'authentification

```
1. Utilisateur s'enregistre (POST /register)
   ↓
2. Utilisateur se connecte (POST /auth/login)
   ↓
3. API retourne un JWT token
   ↓
4. Utilisateur utilise le token dans les en-têtes
   ↓
5. API authentifie la requête
   ↓
6. Utilisateur peut accéder aux routes protégées
   ↓
7. Rafraîchir le token si expiré (POST /auth/refresh-token)
   ↓
8. Se déconnecter pour invalider le token (POST /auth/logout)
```

---

## 🆘 Dépannage

### "Address already in use" sur le port 8000
```bash
# Le port 8000 est occupé, le serveur utilise 8001
# C'est normal et prévu

# Pour vérifier quel processus utilise le port :
lsof -i :8000

# Pour arrêter le processus :
kill -9 <PID>
```

### "Authentication failed"
- Vérifier que le token est dans le header `Authorization: Bearer`
- Vérifier que le token n'a pas expiré
- Rafraîchir le token avec `POST /auth/refresh-token`

### "Token not provided"
- Vérifier le header `Authorization`
- Format correct: `Bearer eyJ0eXAi...`

---

## 📚 Documentation liée

- `API_DOCUMENTATION.md` - Documentation complète (français)
- `DOCUMENTATION_SUMMARY.md` - Résumé et guide
- `storage/api-docs/swagger.yaml` - Spécification OpenAPI
- `OMPAY.postman_collection.json` - Collection Postman

---

**Dernière mise à jour:** 12 novembre 2025  
**Version:** 1.0.0  
**Port:** 8001 (au lieu de 8000)

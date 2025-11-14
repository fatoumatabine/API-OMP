# OMPAY Deployment Instructions

## Image Docker disponible

```
fatoumatbinetousylla/ompay:latest
fatoumatbinetousylla/ompay:v1.0.0
```

## Prérequis de déploiement

✅ **Tous les mises à jour ont été effectuées:**

### 1. Annotations Swagger complètes
- ✅ CompteController: endpoints `/compte/{id}/payment` et `/compte/{id}/transactions`
- ✅ TransactionController: endpoints `/compte/{id}/transactions` et `/compte/{id}/payment-merchant`
- ✅ AuthController: séparation des requêtes OTP (LoginVerifyOtpRequest vs VerifyOtpRequest)
- ✅ Documentation auto-générée via L5 Swagger

### 2. Routes API
- ✅ Routes protégées avec authentification JWT
- ✅ Rate limiting sur tous les endpoints
- ✅ Logging et audit trail

### 3. Sécurité
- ✅ CORS configuré
- ✅ JWT Authentication (Tymon/JWT-Auth)
- ✅ Password hashing avec Bcrypt
- ✅ OTP 2FA pour authentification
- ✅ Environnement de production configuré

### 4. Base de données
- ✅ Migrations optimisées
- ✅ Models avec relations
- ✅ Validation des requêtes

## Déploiement avec Docker

### Option 1: Docker Compose (Recommandé)

```bash
docker-compose up -d
```

### Option 2: Docker simple

```bash
docker run -d \
  --name ompay \
  -p 80:80 \
  -e APP_ENV=production \
  -e APP_URL=https://votre-domaine.com \
  -e DB_HOST=votre-db-host \
  -e DB_PASSWORD=votre-password \
  -e JWT_SECRET=votre-secret-jwt \
  fatoumatbinetousylla/ompay:latest
```

## Variables d'environnement requises

```env
APP_NAME=OMPAY
APP_ENV=production
APP_KEY=base64:... (généré automatiquement)
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=pgsql
DB_HOST=votre-db-host
DB_PORT=5432
DB_DATABASE=ompay
DB_USERNAME=ompay
DB_PASSWORD=votre-password

# JWT
JWT_SECRET=votre-secret-jwt
JWT_ALGORITHM=HS256
JWT_LIFETIME=60

# Email
MAIL_MAILER=smtp
MAIL_HOST=votre-smtp-host
MAIL_PORT=587
MAIL_USERNAME=votre-email
MAIL_PASSWORD=votre-password

# L5 Swagger
L5_SWAGGER_GENERATE_ALWAYS=false
L5_SWAGGER_CONST_HOST=https://votre-domaine.com
```

## Post-déploiement

1. **Migrations**
```bash
docker exec ompay php artisan migrate --force
```

2. **Cache**
```bash
docker exec ompay php artisan config:cache
docker exec ompay php artisan route:cache
```

3. **Documentation Swagger**
Accédez à: `https://votre-domaine.com/api/documentation`

## Tests d'API

### 1. Enregistrement
```bash
curl -X POST https://votre-domaine.com/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+221765557032",
    "first_name": "Fatoumat",
    "last_name": "Sylla",
    "email": "email@example.com",
    "pin_code": "1234",
    "cni_number": "1234567890ABC"
  }'
```

### 2. Connexion
```bash
curl -X POST https://votre-domaine.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone_number": "+221765557032"}'
```

### 3. Vérification OTP
```bash
curl -X POST https://votre-domaine.com/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+221765557032",
    "otp": "123456"
  }'
```

## Support

Documentation API: `/api/documentation`
Health Check: `/api/health`
Detailed Health: `/api/health/detailed`

## Changements récents

- ✅ Annotations Swagger pour tous les endpoints
- ✅ Correction OTP verification (separation de LoginVerifyOtpRequest)
- ✅ Routes pour transactions et paiements
- ✅ Configuration production optimisée
- ✅ Image Docker poussée vers Hub

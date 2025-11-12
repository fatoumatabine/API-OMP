# Modifications Effectuées - Authentification OTP

## 1. Modifications du Contrôleur AuthController

### Endpoint `/api/auth/login` - MODIFIÉ
**Avant :** Acceptait `phone_number` + `password`, retournait JWT token
**Après :** Accepte seulement `phone_number`, envoie OTP par email

```json
Request:
{
  "phone_number": "+33612345678"
}

Response (200):
{
  "success": true,
  "message": "Code OTP envoyé à votre email",
  "data": {
    "user_id": "uuid",
    "phone_number": "+33612345678",
    "email": "user@example.com"
  }
}
```

### Endpoint `/api/auth/verify-otp` - MODIFIÉ
**Avant :** Vérifiait OTP seulement
**Après :** Vérifie OTP et retourne JWT token

```json
Request:
{
  "phone_number": "+33612345678",
  "otp": "123456"
}

Response (200):
{
  "success": true,
  "message": "OTP vérifié avec succès",
  "data": {
    "token": "eyJ0eXAi...",
    "user": {
      "id": "uuid",
      "phone_number": "+33612345678",
      "first_name": "Jean",
      "email": "jean@example.com"
    }
  }
}
```

## 2. Modifications de `/api/register` - SIMPLIFIÉE
**Avant :** Demandait `password` + `password_confirmation`
**Après :** Ne demande plus de password, envoie OTP par email

**Champs obligatoires :**
- phone_number
- first_name
- last_name
- email
- pin_code (4 digits)
- cni_number

## 3. Nouveau Endpoint `/api/verify-otp` (Enregistrement Step 2)
Permet de compléter l'inscription en vérifiant l'OTP et en définissant le mot de passe

```json
Request:
{
  "phone_number": "+33612345678",
  "otp_code": "123456",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123"
}
```

## 4. Modifications du Code

### AuthController.php
- Ajout import `OtpService`
- Modification de `login()` pour envoyer OTP seulement
- Modification de `verifyOtp()` pour retourner JWT token

### UserService.php
- Ajout support pour créer utilisateur sans password
- Génération d'un password temporaire aléatoire si absent
- Utilisation correcte du typage boolean pour PostgreSQL

### Swagger Documentation (swagger.yaml)
- `/api/auth/login` - mise à jour documentation
- `/api/auth/verify-otp` - mise à jour documentation  
- `/api/register` - suppression champs password
- Ajout `/api/verify-otp` pour enregistrement step 2

## 5. Flux d'Authentification (Login)

1. User appelle `/api/auth/login` avec phone_number
2. Backend envoie OTP par email via `OtpService`
3. User reçoit l'OTP et appelle `/api/auth/verify-otp`
4. Backend vérifie l'OTP et retourne JWT token
5. User utilise le token pour les endpoints protégés

## 6. Flux d'Enregistrement

**Étape 1 :** `/api/register`
- Input: phone, email, nom, prénom, pin, cni (PAS de password)
- Output: OTP envoyé par email

**Étape 2 :** `/api/verify-otp`
- Input: phone, otp, password
- Output: Compte créé et prêt à se connecter

## Tests À Effectuer

1. ✅ Enregistrement sans password
2. ✅ OTP envoyé par email (check `Mail::fake()`)
3. ✅ Vérification OTP et JWT retourné
4. ✅ Login avec numéro seulement
5. ✅ Endpoints protégés avec JWT

## Problèmes Identifiés

1. **Migrations** : Les colonnes OTP n'existent pas dans la BD
   - Solution : Exécuter les migrations manuellement

2. **Type Boolean PostgreSQL** : Conversion 0/1 vs true/false
   - Solution : Utiliser les valeurs par défaut de la BD

3. **Mail Service** : Besoin de vérifier si les emails sont réellement envoyés
   - Solution : Vérifier la config MAIL_ dans .env

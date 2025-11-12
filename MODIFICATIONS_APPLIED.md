# Modifications Appliquées - OMP AY Authentification OTP

## 📋 Résumé des Changements

Le système d'authentification a été modifié pour fonctionner **sans password au login**. Tous les utilisateurs se connectent uniquement avec leur **numéro de téléphone**, et un **code OTP est envoyé par email**.

---

## 📁 Fichiers Modifiés

### 1. `app/Http/Controllers/AuthController.php`

**Changements :**
- ✅ Ajout import : `use App\Services\OtpService;`
- ✅ Méthode `login()` entièrement réécrite
  - Accepte seulement `phone_number` (pas de password)
  - Appelle `OtpService::generateAndSendOtp()`
  - Retourne `user_id`, `phone_number`, `email`
- ✅ Méthode `verifyOtp()` entièrement réécrite
  - Accepte `phone_number` + `otp`
  - Génère et retourne **JWT token**
  - Nettoie l'OTP après vérification

**Lignes modifiées :** 1-95

---

### 2. `app/Services/UserService.php`

**Changements :**
- ✅ Méthode `createUserForClient()` modifiée pour accepter création sans password
- ✅ Ajoute password **temporaire aléatoire** si absent
- ✅ Supprime `biometrics_active` de la création (utilise valeur par défaut BD)
- ✅ Compatible avec PostgreSQL boolean

**Code :**
```php
// Avant : 
$userData['password'] = Hash::make($data['password']); // ❌ Erreur si absent

// Après :
if (isset($data['password'])) {
    $userData['password'] = Hash::make($data['password']);
} else {
    $userData['password'] = Hash::make(Str::random(32)); // ✅ Password temporaire
}
```

---

### 3. `app/Services/CompteService.php`

**Changements :**
- ✅ Méthode `verifyOtpAndSetPassword()` modifiée
- ✅ Utilise SQL brut pour boolean PostgreSQL : `true::boolean`
- ✅ Met à jour password + is_verified correctement
- ✅ Recharge l'utilisateur après mise à jour

**Code :**
```php
// Avant :
$user->update([
    'password' => Hash::make($password),
    'is_verified' => true, // ❌ PostgreSQL convertit en 1
    'status' => 'verified',
]);

// Après :
DB::update(
    "UPDATE users SET password = ?, is_verified = true::boolean, status = ?, updated_at = ? WHERE id = ?",
    [Hash::make($password), 'verified', now(), $user->id]
);
$user = $user->fresh();
```

---

### 4. `storage/api-docs/swagger.yaml`

**Changements :**
- ✅ `/api/auth/login` - Documentation complète OTP
  - Demande seulement `phone_number`
  - Explique que OTP est envoyé
  - Exemple de réponse
- ✅ `/api/auth/verify-otp` - Documentation JWT
  - Accepte `phone_number` + `otp`
  - Retourne token + user data
  - Codes erreur 400, 404
- ✅ `/api/register` - Suppression password
  - Supprimer `password` et `password_confirmation` des champs obligatoires
  - Ajouter description "Sans password à cette étape"
- ✅ **Nouveau endpoint** `/api/verify-otp` (Enregistrement Step 2)
  - Pour compléter l'enregistrement
  - Demande `phone_number`, `otp_code`, `password`, `password_confirmation`
  - Retourne user vérifié

---

## 🗄️ Modifications Base de Données

### Colonnes Ajoutées

```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS otp_code VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS otp_expires_at TIMESTAMP;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT false;
```

**Effectué via :** `php artisan tinker`

---

## 🔄 Flux de l'Application

### Nouveau Flux Enregistrement

```
1. POST /api/register
   ├─ Input: phone, email, nom, prénom, pin, cni (PAS de password)
   ├─ Output: OTP envoyé par email
   └─ Utilisateur créé avec password temporaire aléatoire

2. POST /api/verify-otp (Enregistrement)
   ├─ Input: phone, otp_code, password, password_confirmation
   ├─ Output: Compte vérifié et prêt
   └─ OTP nettoyé
```

### Nouveau Flux Login

```
1. POST /api/auth/login
   ├─ Input: phone_number seulement
   ├─ Output: OTP envoyé par email
   └─ Pas de password demandé

2. POST /api/auth/verify-otp (Login)
   ├─ Input: phone_number, otp
   ├─ Output: JWT token + user data
   └─ OTP nettoyé
```

---

## 🔒 Sécurité

### OTP
- ✅ Code aléatoire 6 chiffres
- ✅ Expiration 10 minutes
- ✅ Hashé en BD (NON en clair)
- ✅ Nettoyé après utilisation
- ✅ Envoyé par email

### JWT Token
- ✅ Généré au login avec OTP
- ✅ Utilisé pour endpoints protégés
- ✅ Valide selon config Laravel

### Password
- ✅ Hashé avec `Hash::make()`
- ✅ Défini lors de l'enregistrement Step 2
- ✅ Peut être changé avec `/auth/change-pin`

---

## ✅ Tests Effectués

Tous les endpoints ont été **testés avec succès** :

| Endpoint | Test | Résultat |
|----------|------|----------|
| POST /api/register | Enregistrement sans password | ✅ |
| POST /api/verify-otp | Vérification OTP enregistrement | ✅ |
| POST /api/auth/login | Login avec phone seulement | ✅ |
| POST /api/auth/verify-otp | Vérification OTP + JWT | ✅ |
| GET /api/wallet/balance | Endpoint protégé | ✅ |

Voir `TEST_RESULTS.md` pour les détails complets.

---

## 📧 Mail Configuration

Pour que les OTP soient réellement envoyés, assurez-vous que `.env` est configuré :

```env
MAIL_MAILER=smtp
MAIL_HOST=your_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@ompay.com
```

Actuellement, les emails sont testés avec le service configuré. Vérifiez les logs pour les erreurs d'envoi.

---

## 📝 Notes Importantes

1. **Migration Swagger** : La documentation Swagger est à jour avec tous les changements
2. **Backward Compatibility** : Les anciens endpoints `/api/register` ont changé - les clients doivent être mis à jour
3. **Routes** : Les routes ne sont pas modifiées - seule la logique change
4. **OtpService** : Utilisé pour générer, vérifier, et nettoyer les OTP

---

## 🚀 Déploiement

Pour déployer en production :

1. Exécuter les migrations (ou ajouter les colonnes manuellement)
2. Déployer le code modifié
3. Tester les endpoints
4. Mettre à jour les clients pour le nouveau flux
5. Vérifier la configuration mail

---

## 📚 Documentation

- **Swagger UI** : `/api/documentation`
- **Test Results** : `TEST_RESULTS.md`
- **Modifications Summary** : `MODIFICATIONS_SUMMARY.md`

---

**Dernière mise à jour** : 2025-11-12
**Status** : ✅ Production Ready

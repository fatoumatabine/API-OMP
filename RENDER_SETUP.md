# Configuration Render - OMPAY

## Erreur: PostgreSQL Connection Refused

**Cause:** Les variables d'environnement PostgreSQL ne sont pas configurées dans Render.

## Solution: Ajouter les variables d'environnement dans Render

### Étape 1: Allez sur le Dashboard Render
https://dashboard.render.com

### Étape 2: Sélectionnez votre Service
- Service ID: `srv-d490dkfdiees73a7hem0`
- Service Name: `ompay`

### Étape 3: Cliquez sur l'onglet "Environment"

### Étape 4: Ajoutez une nouvelle variable
Vous avez deux options:

#### **Option A: Utiliser DATABASE_URL (Recommandé)**
```
DATABASE_URL=postgresql://[USERNAME]:[PASSWORD]@[HOST]:[PORT]/[DATABASE]?sslmode=require
```

Exemple avec Neon (PostgreSQL gratuit):
```
DATABASE_URL=postgresql://neondb_owner:YOUR_PASSWORD@ep-purple-meadow-a4uzmiy3.us-east-1.aws.neon.tech/neondb?sslmode=require
```

#### **Option B: Variables individuelles**
Si DATABASE_URL ne fonctionne pas, ajoutez:
```
DB_CONNECTION=pgsql
DB_HOST=ep-purple-meadow-a4uzmiy3.us-east-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=YOUR_PASSWORD
```

### Étape 5: Redéployer

Après avoir ajouté les variables:

1. Cliquez sur **"Manual Deploy"**
2. Attendez 2-3 minutes
3. Vérifiez les logs pour confirmer

### Vérifier la Connexion

Une fois déployé, testez:
```bash
curl https://ompay-4mgy.onrender.com/api/auth/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+33612345678","password":"test"}'
```

## Autres Variables Essentielles

Si vous en avez besoin, ajoutez aussi:

```
JWT_SECRET=your_jwt_secret_key_here
APP_KEY=base64:YOUR_APP_KEY
APP_URL=https://ompay-4mgy.onrender.com
LOG_LEVEL=debug
TWILIO_ACCOUNT_SID=YOUR_TWILIO_SID
TWILIO_AUTH_TOKEN=YOUR_TWILIO_TOKEN
TWILIO_PHONE_NUMBER=YOUR_TWILIO_NUMBER
```

## Démarrage des Services PostgreSQL Gratuit

### Neon (Recommandé - Gratuit)
1. Allez sur https://neon.tech
2. Créez un compte gratuit
3. Créez un projet PostgreSQL
4. Copiez la `DATABASE_URL`

### Supabase (Alternative)
1. Allez sur https://supabase.io
2. Créez un projet gratuit
3. Utilisez la connection string PostgreSQL

## Si toujours une erreur

Vérifiez les logs Render:
1. Allez dans votre service
2. Onglet **"Logs"**
3. Cherchez l'erreur exacte
4. Vérifiez que les credentials sont corrects

## Notes Importantes

- ⚠️ Ne commitez pas `.env.production` avec les vrais secrets
- ✓ Utilisez `DATABASE_URL` plutôt que des variables individuelles
- ✓ Assurez-vous que le host PostgreSQL est accessible depuis Internet
- ✓ Vérifiez les firewall rules si vous utilisez une base locale

---

Une fois configuré, tout devrait fonctionner! 🚀

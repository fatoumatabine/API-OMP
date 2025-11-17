#!/bin/sh

echo "Starting OMPAY application..."



# Force clear all caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Optimiser les caches (sans DB)
echo "Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Générer la documentation Swagger
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate --quiet || true

# Configurer le cache (optionnel)
echo "Configuring application..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Essayer les migrations (non-bloquant)
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "Migration warning, continuing..."
echo "Migration completed or skipped"

# Démarrer les services
echo "Starting Nginx and PHP-FPM..."
nginx -g "daemon off;" &
NGINX_PID=$!

php-fpm -F &
FPM_PID=$!

# Démarrer le queue worker (pour les jobs en arrière-plan)
echo "Starting queue worker..."
php artisan queue:work --queue=default,otp --tries=3 &
QUEUE_PID=$!

# Attendre les processus
wait $NGINX_PID $FPM_PID $QUEUE_PID

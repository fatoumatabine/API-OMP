#!/bin/sh

echo "Starting OMPAY application..."

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
php artisan migrate --force 2>/dev/null || echo "Database not ready yet, continuing..."

# Démarrer les services
echo "Starting Nginx and PHP-FPM..."
nginx -g "daemon off;" &
NGINX_PID=$!

php-fpm -F &
FPM_PID=$!

# Attendre les processus
wait $NGINX_PID $FPM_PID

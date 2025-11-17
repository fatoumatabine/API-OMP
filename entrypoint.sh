#!/bin/sh

echo "Starting OMPAY application..."

# Créer .env s'il n'existe pas
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from environment variables..."
    cat > /var/www/html/.env << EOF
APP_NAME=OMPAY
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-https://ompay-4mgy.onrender.com}
DB_CONNECTION=pgsql
DATABASE_URL=${DATABASE_URL:-}
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
EOF
    echo ".env created successfully"
fi

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

# Attendre les processus
wait $NGINX_PID $FPM_PID

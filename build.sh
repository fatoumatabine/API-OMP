#!/bin/bash
set -e

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate Swagger/OpenAPI documentation
php artisan l5-swagger:generate

# Copy to public directory for web server access
cp storage/api-docs/api-docs.json public/api-docs.json || true

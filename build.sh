#!/bin/bash
set -e

# Generate Swagger/OpenAPI documentation
php artisan l5-swagger:generate

# Copy to public directory for web server access
cp storage/api-docs/api-docs.json public/api-docs.json || true

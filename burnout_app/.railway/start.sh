#!/bin/bash
set -e

# Clear and cache config
php artisan config:clear || true
php artisan cache:clear || true

# Generate APP_KEY if not set (should be set via env var, but just in case)
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY not set. Generating one..."
    php artisan key:generate --force || true
fi

# Run migrations (if database is configured)
php artisan migrate --force || true

# Start the server
exec php artisan serve --host=0.0.0.0 --port=$PORT


#!/bin/sh
set -e

# Support Render dynamic PORT
if [ -n "$PORT" ]; then
    sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/nginx.conf
    sed -i "s/listen \[::\]:80;/listen \[::\]:$PORT;/g" /etc/nginx/nginx.conf
fi

# Ensure no conflicting default configs remain
rm -rf /etc/nginx/conf.d/* /etc/nginx/http.d/*

# Verify Nginx configuration syntax
nginx -t

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public/hero-slides \
         /var/www/html/storage/app/public/categories \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If SQLite is used, ensure database file exists
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Storage symlink
php artisan storage:link --force || true

# Run database migrations unconditionally
echo "Running database migrations..."
php artisan migrate --force || true

# Seed database only if it is empty (prevents duplicate data on restarts)
echo "Checking if database needs seeding..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1 | tr -d '[:space:]')
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Database is empty, seeding now..."
    php artisan db:seed --force || true
else
    echo "Database already has $USER_COUNT user(s), skipping seed."
fi

# Production optimization caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:cache || true
php artisan view:cache || true
php artisan config:cache || true

# Execute supervisor to start PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

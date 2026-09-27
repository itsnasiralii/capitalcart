#!/bin/sh
set -e

# Parse DATABASE_URL into individual DB_* variables (Render provides DATABASE_URL)
# Format: postgresql://username:password@host:port/dbname
if [ -n "$DATABASE_URL" ]; then
    # Strip the scheme (postgres:// or postgresql://)
    DB_URL_STRIPPED=$(echo "$DATABASE_URL" | sed 's|^postgresql://||;s|^postgres://||')
    # Extract user:password
    DB_USERINFO=$(echo "$DB_URL_STRIPPED" | cut -d'@' -f1)
    # Extract host:port/dbname
    DB_HOSTINFO=$(echo "$DB_URL_STRIPPED" | cut -d'@' -f2)

    export DB_CONNECTION=pgsql
    export DB_USERNAME=$(echo "$DB_USERINFO" | cut -d':' -f1)
    export DB_PASSWORD=$(echo "$DB_USERINFO" | cut -d':' -f2)
    export DB_HOST=$(echo "$DB_HOSTINFO" | cut -d':' -f1)
    export DB_PORT=$(echo "$DB_HOSTINFO" | cut -d':' -f2 | cut -d'/' -f1)
    export DB_DATABASE=$(echo "$DB_HOSTINFO" | cut -d'/' -f2)
    export DB_SSLMODE=require

    echo "Database configured: host=$DB_HOST port=$DB_PORT db=$DB_DATABASE user=$DB_USERNAME"
fi

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
         /var/www/html/storage/app/private/livewire-tmp \
         /var/www/html/storage/app/public/hero-slides \
         /var/www/html/storage/app/public/categories \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# FIX: Create nginx temp directories with correct permissions
# Without this, nginx fails with "Permission denied" on /var/lib/nginx/tmp/client_body
# when handling file uploads (images), causing HTTP 500 errors.
mkdir -p /tmp/nginx_client_body \
         /tmp/nginx_proxy \
         /tmp/nginx_fastcgi \
         /tmp/nginx_uwsgi \
         /tmp/nginx_scgi
chmod 777 /tmp/nginx_client_body /tmp/nginx_proxy /tmp/nginx_fastcgi /tmp/nginx_uwsgi /tmp/nginx_scgi

# If SQLite is used, ensure database file exists
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Storage symlink
php artisan storage:link --force || true

# Run database migrations unconditionally
echo "Running database migrations..."
php artisan migrate --force

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

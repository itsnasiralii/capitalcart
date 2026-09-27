# ==============================================================================
# Multi-Stage Dockerfile for CapitalCart.pk on Render.com
# ==============================================================================

# Stage 1: Frontend Asset Compilation (Vite)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --silent
COPY . .
RUN npm run build

# Stage 2: PHP 8.3 + Nginx + Production Runtime
FROM php:8.3-fpm-alpine

# Install OS dependencies, Nginx, Supervisor, and database development libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    postgresql-dev \
    sqlite-dev \
    oniguruma-dev \
    libxml2-dev

# Configure and install PHP extensions (supports MySQL, PostgreSQL, SQLite)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        mbstring \
        zip \
        bcmath \
        intl \
        opcache \
        gd \
        xml \
        pcntl

# Allow large source images; the application optimizes them before database storage.
RUN printf "upload_max_filesize=25M\npost_max_size=30M\nmemory_limit=256M\nmax_execution_time=300\nlog_errors=On\nerror_log=/proc/self/fd/2\n" \
    > /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy Vite-compiled production assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Install PHP production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy container configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
RUN rm -rf /etc/nginx/conf.d/* /etc/nginx/http.d/*
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set directory permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose standard web port
EXPOSE 80

# Run entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

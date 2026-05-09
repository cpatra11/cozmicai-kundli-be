# Use PHP 8.4 FPM on Debian Bookworm (native arm64 support)
FROM php:8.4-fpm

WORKDIR /var/www/api

ENV APP_ENV=prod \
    APP_DEBUG=0

# Install system deps
RUN apt-get update && apt-get install -y \
    gcc \
    make \
    libc-dev \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    curl \
    unzip \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Install Caddy from official repo
RUN set -eux; \
    curl -fsSL https://dl.cloudsmith.io/public/caddy/stable/gpg.key | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg; \
    echo "deb [signed-by=/usr/share/keyrings/caddy-stable-archive-keyring.gpg] https://dl.cloudsmith.io/public/caddy/stable/deb/debian any-version main" > /etc/apt/sources.list.d/caddy-stable.list; \
    apt-get update && apt-get install -y caddy && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    intl \
    mbstring \
    gd \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy dependency manifests first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Run composer scripts with full app in place
RUN composer dump-autoload --optimize

# Compile swetest binary
RUN cd swetest/src && make clean && make swetest && chmod +x swetest

# Cache + permissions
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var
RUN php bin/console cache:warmup --no-optional-warmers 2>/dev/null || true
RUN chown -R www-data:www-data var

# Copy Caddyfile and start script
COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /usr/local/bin/

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]

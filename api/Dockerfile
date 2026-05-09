FROM dunglas/frankenphp:1.12.2-php8

WORKDIR /var/www/api

ENV APP_ENV=prod \
    APP_DEBUG=0

RUN apt-get update && apt-get install -y \
    gcc \
    make \
    libc-dev \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    bcmath \
    intl \
    mbstring \
    gd \
    zip \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN composer dump-autoload --optimize

RUN cd swetest/src && make clean && make swetest && chmod +x swetest

RUN mkdir -p var/cache var/log && chmod -R 777 var
RUN frankenphp php-cli bin/console cache:warmup --no-optional-warmers 2>/dev/null || true
RUN chmod -R 777 var

COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

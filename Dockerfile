# syntax=docker/dockerfile:1
# ══════════════════════════════════════════════════════════════════
# Stage 1 — deps-php
#   Install OS libraries & PHP extensions. No application code.
# ══════════════════════════════════════════════════════════════════
FROM php:8.3-fpm-alpine AS deps-php

RUN --mount=type=cache,id=apk-cache,target=/var/cache/apk \
    apk add --no-cache \
        git \
        curl \
        netcat-openbsd \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        zip \
        unzip \
        oniguruma-dev \
        icu-dev \
        libxml2-dev \
        linux-headers \
        postgresql-dev

RUN --mount=type=cache,id=apk-cache,target=/var/cache/apk \
    --mount=type=cache,id=pecl-cache,target=/tmp/pear \
    # install build tools as virtual package so they can be removed cleanly
    apk add --no-cache --virtual .build-deps autoconf g++ make \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    # remove build tools — runtime image stays lean
    && apk del .build-deps

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini


# ══════════════════════════════════════════════════════════════════
# Stage 2 — deps-composer
#   Install Composer vendor dependencies (no app code yet).
# ══════════════════════════════════════════════════════════════════
FROM deps-php AS deps-composer

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN --mount=type=cache,id=composer-cache,target=/root/.composer/cache \
    composer install \
        --no-scripts \
        --no-interaction \
        --prefer-dist


# ══════════════════════════════════════════════════════════════════
# Stage 3 — deps-node
#   Build frontend assets with Vite.
# ══════════════════════════════════════════════════════════════════
FROM node:20-alpine AS deps-node

WORKDIR /app

COPY package*.json ./
RUN --mount=type=cache,id=npm-cache,target=/root/.npm \
    npm ci

COPY . .
RUN npm run build


# ══════════════════════════════════════════════════════════════════
# Stage 4 — app-local
#   Development image — source is bind-mounted at runtime;
#   vendor layer cached here.
# ══════════════════════════════════════════════════════════════════
FROM deps-php AS app-local

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=deps-composer /var/www/html/vendor /var/www/html/vendor

# Copy source so dump-autoload can build proper classmap
COPY . /var/www/html/
RUN composer dump-autoload --optimize
# Source akan di-override oleh bind-mount saat runtime

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]


# ══════════════════════════════════════════════════════════════════
# Stage 5 — app-production
#   Immutable production image — all artefacts baked in.
# ══════════════════════════════════════════════════════════════════
FROM deps-php AS app-production

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=deps-composer /var/www/html/vendor /var/www/html/vendor

WORKDIR /var/www/html

COPY . .
COPY --from=deps-node /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Сначала только манифесты — слой с зависимостями переиспользуется между сборками.
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-progress --no-scripts

COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "backend/public", "backend/public/router.php"]

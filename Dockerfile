FROM php:8.2-apache

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    xml

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf || true \
    && a2enmod rewrite

# Установка рабочих прав
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

# Копирование composer файлов
COPY composer.json composer.lock ./

# Установка зависимостей
RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload --optimize

# Копирование файлов приложения
COPY . .

# Установка прав
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var

# Создание миграций (только если переменная не задана)
RUN if [ "$RUN_TESTS" != "true" ]; then php bin/console doctrine:migrations:migrate --no-interaction || true; fi

EXPOSE 80

# Скрипт для запуска тестов перед стартом приложения
RUN echo '#!/bin/bash' > /start-app.sh \
    && echo 'set -e' >> /start-app.sh \
    && echo 'if [ "$RUN_TESTS" = "true" ]; then' >> /start-app.sh \
    && echo '  echo "Running tests..."' >> /start-app.sh \
    && echo '  php bin/console doctrine:database:create --if-not-exists' >> /start-app.sh \
    && echo '  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration' >> /start-app.sh \
    && echo '  php bin/phpunit --testdox' >> /start-app.sh \
    && echo '  TEST_RESULT=$?' >> /start-app.sh \
    && echo '  if [ $TEST_RESULT -ne 0 ]; then' >> /start-app.sh \
    && echo '    echo "Tests failed!"' >> /start-app.sh \
    && echo '    exit 1' >> /start-app.sh \
    && echo '  fi' >> /start-app.sh \
    && echo '  echo "All tests passed!"' >> /start-app.sh \
    && echo 'else' >> /start-app.sh \
    && echo '  echo "Skipping tests..."' >> /start-app.sh \
    && echo '  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration' >> /start-app.sh \
    && echo 'fi' >> /start-app.sh \
    && echo 'exec apache2-foreground' >> /start-app.sh \
    && chmod +x /start-app.sh

CMD ["/start-app.sh"]
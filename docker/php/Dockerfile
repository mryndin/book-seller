FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        gd \
        zip \
        intl \
        bcmath \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create working directory
WORKDIR /var/www/app

# Setup www-data user
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Create entrypoint script inline (avoid CRLF issues)
RUN echo '#!/bin/bash\n\
chmod -R 777 /var/www/app/frontend/runtime 2>/dev/null || true\n\
chmod -R 777 /var/www/app/backend/runtime 2>/dev/null || true\n\
chmod -R 777 /var/www/app/common/runtime 2>/dev/null || true\n\
chmod -R 777 /var/www/app/console/runtime 2>/dev/null || true\n\
chmod -R 777 /var/www/app/frontend/web 2>/dev/null || true\n\
chmod -R 777 /var/www/app/backend/web 2>/dev/null || true\n\
mkdir -p /var/www/app/frontend/web/uploads 2>/dev/null || true\n\
mkdir -p /var/www/app/backend/web/uploads 2>/dev/null || true\n\
chmod -R 777 /var/www/app/frontend/web/uploads 2>/dev/null || true\n\
chmod -R 777 /var/www/app/backend/web/uploads 2>/dev/null || true\n\
if [ -f "/var/www/app/composer.json" ]; then\n\
    cd /var/www/app\n\
    composer install --no-interaction --optimize-autoloader\n\
    composer require --no-interaction --optimize-autoloader php-amqplib/php-amqplib:^3.5 yiisoft/yii2-imagine:^2.3\n\
fi\n\
exec php-fpm' > /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]